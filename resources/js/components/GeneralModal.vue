<template>
    <Modal
        :show="true"
        class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden"
        size="4xl"
        role="dialog"
        @close="handleClose"
    >
        <ModalHeader v-text="__('novaFontawesome.modalTitle')" />

        <ModalContent class="space-y-2 px-6">
            <div class="flex w-full gap-4">
                <div class="flex w-1/2 gap-4">
                    <div class="w-1/2">
                        <SelectControl
                            v-model="filter.family"
                            :options="familyOptions"
                        />
                    </div>
                    <div class="w-1/2">
                        <SelectControl
                            v-model="filter.style"
                            :options="styleOptions"
                        />
                    </div>
                </div>
                <div class="w-1/2">
                    <input
                        type="text"
                        id="search"
                        class="w-full form-control form-input form-control-bordered"
                        :placeholder="__('novaFontawesome.search.placeholder')"
                        v-model="filter.search"
                        @input="debouncedSearch"
                    />
                </div>
            </div>
            <div
                class="fontawesome-inner overflow-y-auto"
                style="max-height: 60vh"
                @scroll="onScroll"
                id="iconContainer"
            >
                <div v-if="isLoading">
                    <div class="flex flex-wrap items-stretch">
                        <div
                            v-for="n in 24"
                            :key="'skeleton-' + n"
                            class="inner flex items-center justify-center text-center icon-box"
                        >
                            <div class="p-2 w-full">
                                <div
                                    class="icon-svg-container skeleton-box animate-pulse bg-gray-200 dark:bg-gray-700 rounded"
                                ></div>
                                <span
                                    class="icon-name skeleton-text animate-pulse bg-gray-200 dark:bg-gray-700 rounded block mt-2"
                                    >&nbsp;</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="flex flex-wrap items-stretch"
                    v-else-if="chunkedIcons.length > 0 && !isLoading"
                >
                    <div
                        v-for="(icon, index) in chunkedIcons"
                        :key="icon.id || index"
                        class="inner flex flex-col items-center justify-center text-center icon-box cursor-pointer transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                        @click="saveIcon(icon)"
                    >
                        <div
                            class="icon-svg-container"
                            v-html="getIconSvg(icon)"
                        ></div>
                        <span class="icon-name" v-html="icon.id"></span>
                    </div>
                </div>
                <div
                    v-else-if="
                        !isLoading &&
                        filter.search.length > 0 &&
                        filter.search.length >= minSearchLength
                    "
                    class="py-6 text-center text-md"
                >
                    {{ __("novaFontawesome.noResults") }}
                </div>
                <div
                    v-else-if="
                        !isLoading &&
                        filter.search.length > 0 &&
                        filter.search.length < minSearchLength
                    "
                    class="py-6 text-center text-md"
                >
                    {{ __("novaFontawesome.searchPrompt") }}
                </div>
            </div>
        </ModalContent>

        <ModalFooter>
            <div class="ml-auto flex gap-3">
                <Button type="button" variant="ghost" @click="handleClose">
                    {{ __("novaFontawesome.cancel") }}
                </Button>

                <Button
                    type="button"
                    variant="solid"
                    :disabled="isLoading || !value"
                    :loading="isLoading"
                    @click="handleConfirm"
                >
                    {{ __("novaFontawesome.save") }}
                </Button>
            </div>
        </ModalFooter>
    </Modal>
</template>

<script>
    import { FormField, HandlesValidationErrors } from "laravel-nova";
    import { Button } from "laravel-nova-ui";
    import debounce from "lodash/debounce";

    export default {
        name: "GeneralModal",
        mixins: [FormField, HandlesValidationErrors],
        components: {
            Button,
        },
        props: ["field"],
        data: () => ({
            isLoading: false,
            icons: [],
            iconsChunked: [],
            expanded: false,
            chunk: 0,
            value: "",
            selectedSvg: null,
            filter: {
                family: "all",
                style: "all",
                search: "",
            },
            availableFamilies: [],
            availableStyles: [],
            debouncedSearch: null,
        }),
        computed: {
            pro() {
                return this.field.pro || false;
            },
            minSearchLength() {
                return this.field.minSearchLength || 2;
            },
            familyOptions() {
                const placeholder = {
                    value: "all",
                    label: this.__("novaFontawesome.selectFamily.placeholder"),
                };
                return [
                    placeholder,
                    ...this.availableFamilies.map((f) => ({
                        value: f.id,
                        label: f.label,
                    })),
                ];
            },
            styleOptions() {
                const placeholder = {
                    value: "all",
                    label: this.__("novaFontawesome.selectStyle.placeholder"),
                };
                return [
                    placeholder,
                    ...this.availableStyles.map((s) => ({
                        value: s.id,
                        label: s.label,
                    })),
                ];
            },
            filteredIcons() {
                let filtered = this.icons;

                if (this.filter.style !== "all") {
                    filtered = filtered.filter((icon) => {
                        const freeStyles =
                            icon.familyStylesByLicense?.free || [];
                        return freeStyles.some(
                            (s) => s.style === this.filter.style
                        );
                    });
                }

                if (this.filter.family !== "all") {
                    filtered = filtered.filter((icon) => {
                        const freeStyles =
                            icon.familyStylesByLicense?.free || [];
                        return freeStyles.some(
                            (s) => s.family === this.filter.family
                        );
                    });
                }

                return filtered;
            },
            chunkedIcons() {
                return this.iconsChunked;
            },
            defaultIcon() {
                return this.field.default_icon || "";
            },
            defaultIconType() {
                return this.field.default_icon_type || "";
            },
            enforceDefaultIcon() {
                return this.field.enforce_default_icon || false;
            },
            defaultIconOutput() {
                return this.defaultIconType + " fa-" + this.defaultIcon;
            },
        },
        created() {
            this.debouncedSearch = debounce(this.searchIcons, 300);
        },
        async mounted() {
            // Load metadata and popular icons on mount with loading state
            this.isLoading = true;
            await this.loadMetadata();
            this.loadPopularIcons();
        },
        methods: {
            async loadMetadata() {
                try {
                    const params = {
                        version: this.field.version || "6.x",
                        freeOnly: this.field.freeOnly !== false,
                    };

                    const { data } = await Nova.request().get(
                        "/nova-vendor/nova-fontawesome/metadata",
                        { params }
                    );

                    if (data.success && data.metadata) {
                        this.availableFamilies = data.metadata.families || [];
                        this.availableStyles = data.metadata.styles || [];
                    }
                } catch (error) {
                    console.error("Error loading metadata:", error);
                }
            },

            async searchIcons() {
                if (this.filter.search.length < this.minSearchLength) {
                    this.icons = [];
                    this.iconsChunked = [];
                    this.chunk = 0;
                    return;
                }

                this.isLoading = true;
                this.chunk = 0;
                this.iconsChunked = [];

                try {
                    const params = {
                        query: this.filter.search,
                        version: this.field.version || "6.x",
                        first: this.field.maxResults || 50,
                        freeOnly: this.field.freeOnly !== false,
                    };

                    if (this.field.styles) {
                        params.styles = this.field.styles;
                    }

                    const { data } = await Nova.request().get(
                        "/nova-vendor/nova-fontawesome/search",
                        { params }
                    );

                    if (data.success) {
                        this.icons = data.icons;
                        this.getChunk();
                    }
                } catch (error) {
                    console.error("Error searching icons:", error);
                    this.icons = [];
                } finally {
                    this.isLoading = false;
                }
            },

            async loadPopularIcons() {
                try {
                    const params = {
                        version: this.field.version || "6.x",
                        first: 24,
                    };

                    const { data } = await Nova.request().get(
                        "/nova-vendor/nova-fontawesome/popular",
                        { params }
                    );

                    if (data.success) {
                        this.icons = data.icons;
                        this.getChunk();
                    }
                } catch (error) {
                    console.error("Error fetching popular icons:", error);
                } finally {
                    this.isLoading = false;
                }
            },

            onScroll({ target: { scrollTop, clientHeight, scrollHeight } }) {
                if (
                    scrollTop + clientHeight >= scrollHeight - 250 &&
                    this.expanded === false
                ) {
                    this.expanded = true;
                    this.getChunk();
                }
            },

            getChunk() {
                const chunkSize = 100;
                const filtered = this.filteredIcons;

                const nextChunk = filtered.slice(
                    this.chunk,
                    this.chunk + chunkSize
                );
                this.iconsChunked = [...this.iconsChunked, ...nextChunk];

                this.expanded = false;
                this.chunk += chunkSize;
            },

            getIconSvg(icon) {
                if (!icon.svgs || icon.svgs.length === 0) {
                    return null;
                }

                // Prefer solid, then regular, then first available
                const preferredOrder = [
                    "solid",
                    "regular",
                    "brands",
                    "light",
                    "thin",
                    "duotone",
                ];

                for (const preferred of preferredOrder) {
                    const svgData = icon.svgs.find(
                        (s) => s.familyStyle?.style === preferred
                    );
                    if (svgData && svgData.pathData) {
                        return this.buildSvgFromPath(svgData);
                    }
                }

                // Fallback to first available
                if (icon.svgs[0] && icon.svgs[0].pathData) {
                    const svgData = icon.svgs[0];
                    return this.buildSvgFromPath(svgData);
                }

                return null;
            },

            buildSvgFromPath(svgData) {
                const style = svgData.familyStyle?.style;
                const family = svgData.familyStyle?.family;
                const pathData = svgData.pathData;
                const width = svgData.width || 512;
                const height = svgData.height || 512;

                if (!pathData || pathData.length === 0) {
                    return `<svg viewBox="0 0 ${width} ${height}"></svg>`;
                }

                // pathData is an array
                // For monotone: only one path (index 0)
                // For duotone: two paths - index 0 is secondary, index 1 is primary
                const isDuotone = style === "duotone" && pathData.length === 2;

                let paths = "";
                if (isDuotone) {
                    // Secondary path (lighter)
                    if (pathData[0]) {
                        paths += `<path d="${pathData[0]}" opacity="0.4"/>`;
                    }
                    // Primary path
                    if (pathData[1]) {
                        paths += `<path d="${pathData[1]}"/>`;
                    }
                } else {
                    // Monotone icon - single path
                    if (pathData[0]) {
                        paths = `<path d="${pathData[0]}"/>`;
                    }
                }

                return `<svg viewBox="0 0 ${width} ${height}" xmlns="http://www.w3.org/2000/svg">${paths}</svg>`;
            },

            getIconFamilyStyle(icon) {
                if (
                    !icon.familyStylesByLicense?.free ||
                    icon.familyStylesByLicense.free.length === 0
                ) {
                    return { family: "classic", style: "solid" };
                }

                const firstAvailable = icon.familyStylesByLicense.free[0];
                return {
                    family: firstAvailable.family || "classic",
                    style: firstAvailable.style || "solid",
                };
            },

            saveIcon(icon) {
                const { family, style } = this.getIconFamilyStyle(icon);

                // Build the CSS class string according to FontAwesome structure
                let classString = "";

                // Handle different families
                if (family === "brands") {
                    // Brands: fa-brands (both family and style)
                    classString = "fa-brands";
                } else if (family === "sharp") {
                    // Sharp: fa-sharp + style class
                    classString = `fa-sharp fa-${style}`;
                } else if (family === "sharp-duotone") {
                    // Sharp Duotone: fa-sharp-duotone + style class
                    classString = `fa-sharp-duotone fa-${style}`;
                } else if (family === "duotone") {
                    // Duotone: fa-duotone + style class
                    classString = `fa-duotone fa-${style}`;
                } else {
                    // Classic (default): just the style class
                    classString = `fa-${style}`;
                }

                this.value = `${classString} fa-${icon.id}`;
                this.selectedSvg = this.getIconSvg(icon);

                this.handleConfirm();
            },

            handleClose() {
                this.$emit("close");
            },

            handleConfirm() {
                this.$emit("confirm", {
                    value: this.value,
                    svg: this.selectedSvg,
                });
            },
        },

        watch: {
            "filter.family": {
                handler() {
                    this.chunk = 0;
                    this.iconsChunked = [];
                    this.getChunk();
                },
            },
            "filter.style": {
                handler() {
                    this.chunk = 0;
                    this.iconsChunked = [];
                    this.getChunk();
                },
            },
        },
    };
</script>

<style scoped>
    .icon-box {
        width: 11.5%;
        aspect-ratio: 4 / 3;
        border: 1px solid rgb(var(--colors-gray-200));
        border-radius: 0.375rem;
        margin: 0.25rem;
        padding: 0.5rem;
    }

    .dark .icon-box {
        border-color: rgb(var(--colors-gray-700));
    }

    .icon-box:hover {
        border-color: rgb(var(--colors-primary-500));
        color: rgb(var(--colors-primary-500));
    }

    .icon-svg-container {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        flex: 1;
    }

    .icon-svg-container :deep(svg) {
        width: 2em;
        height: 2em;
        fill: currentColor;
    }

    .icon-name {
        display: block;
        font-size: 0.75rem;
        margin-top: 0.5rem;
        background: rgb(var(--colors-gray-100));
        padding: 0.25em 0.5em;
        border-radius: 0.25rem;
        color: rgb(var(--colors-gray-700));
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 100%;
    }

    .dark .icon-name {
        background: rgb(var(--colors-gray-700));
        color: rgb(var(--colors-gray-300));
    }

    .skeleton-box {
        width: 100%;
        flex: 1;
        display: inline-block;
    }

    .skeleton-text {
        height: 1.25rem;
    }

    @keyframes pulse {
        0%,
        100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @media (max-width: 1279px) {
        .icon-box {
            width: 24%;
        }
    }

    @media (max-width: 900px) {
        .icon-box {
            width: 49%;
        }
    }
</style>
