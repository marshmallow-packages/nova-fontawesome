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
            <div class="flex gap-4">
                <div class="w-1/2">
                    <SelectControl
                        v-model:selected="filter.type"
                    >
                        <option value="all" selected>
                            {{ __("novaFontawesome.selectType.placeholder") }}
                        </option>
                        <option
                            v-for="def in availableStyles"
                            :key="def"
                            :value="def"
                        >
                            {{ formatStyleName(def) }}
                        </option>
                    </SelectControl>
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
                style="max-height: 60vh;"
                @scroll="onScroll"
                id="iconContainer"
            >
                <div v-if="isLoading">
                    <div class="flex flex-wrap items-stretch">
                        <div
                            v-for="n in 20"
                            :key="'skeleton-' + n"
                            class="inner flex items-center justify-center text-center icon-box"
                        >
                            <div class="p-2 w-full">
                                <div class="icon-svg-container skeleton-box animate-pulse bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <span class="icon-name skeleton-text animate-pulse bg-gray-200 dark:bg-gray-700 rounded block mt-2">&nbsp;</span>
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
                        <div class="icon-svg-container" v-html="getIconSvg(icon)"></div>
                        <span
                            class="icon-name"
                            v-html="icon.id"
                        ></span>
                    </div>
                </div>
                <div v-else-if="!isLoading && filter.search.length > 0 && filter.search.length >= minSearchLength" class="py-6 text-center text-md">
                    {{ __("novaFontawesome.noResults") }}
                </div>
                <div v-else-if="!isLoading && filter.search.length > 0 && filter.search.length < minSearchLength" class="py-6 text-center text-md">
                    {{ __("novaFontawesome.searchPrompt") }}
                </div>
            </div>
        </ModalContent>

        <ModalFooter>
            <div class="ml-auto flex gap-3">
                <Button
                    type="button"
                    variant="ghost"
                    @click="handleClose"
                >
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
    import { Button } from 'laravel-nova-ui';
    import debounce from 'lodash/debounce';

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
                type: "all",
                search: "",
            },
            debouncedSearch: null,
        }),
        computed: {
            pro() {
                return this.field.pro || false;
            },
            minSearchLength() {
                return this.field.minSearchLength || 2;
            },
            availableStyles() {
                const styles = this.field.styles || ['solid', 'regular', 'brands'];
                return styles;
            },
            filteredIcons() {
                if (this.filter.type === 'all') {
                    return this.icons;
                }

                return this.icons.filter(icon => {
                    const freeStyles = icon.familyStylesByLicense?.free || [];
                    return freeStyles.some(s => s.style === this.filter.type);
                });
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
        mounted() {
            // Load popular icons on mount with loading state
            this.isLoading = true;
            this.loadPopularIcons();
        },
        methods: {
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
                        version: this.field.version || '6.x',
                        first: this.field.maxResults || 50,
                        freeOnly: this.field.freeOnly !== false,
                    };

                    if (this.field.styles) {
                        params.styles = this.field.styles;
                    }

                    const { data } = await Nova.request().get('/nova-vendor/nova-fontawesome/search', { params });

                    if (data.success) {
                        this.icons = data.icons;
                        this.getChunk();
                    }
                } catch (error) {
                    console.error('Error searching icons:', error);
                    this.icons = [];
                } finally {
                    this.isLoading = false;
                }
            },

            async loadPopularIcons() {
                try {
                    const params = {
                        version: this.field.version || '6.x',
                        first: 20,
                    };

                    const { data } = await Nova.request().get('/nova-vendor/nova-fontawesome/popular', { params });

                    if (data.success) {
                        this.icons = data.icons;
                        this.getChunk();
                    }
                } catch (error) {
                    console.error('Error fetching popular icons:', error);
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

            formatStyleName(style) {
                if (style === 'all') {
                    return this.__("novaFontawesome.selectType.placeholder");
                }

                const translations = {
                    solid: this.__("novaFontawesome.types.solid"),
                    regular: this.__("novaFontawesome.types.regular"),
                    light: this.__("novaFontawesome.types.light"),
                    thin: this.__("novaFontawesome.types.thin"),
                    duotone: this.__("novaFontawesome.types.duotone"),
                    brands: this.__("novaFontawesome.types.brands"),
                };

                return translations[style] || style.charAt(0).toUpperCase() + style.slice(1);
            },

            getIconSvg(icon) {
                if (!icon.svgs || icon.svgs.length === 0) {
                    return '<svg viewBox="0 0 24 24"><rect fill="#ccc" width="24" height="24" rx="4"/></svg>';
                }

                // Prefer solid, then regular, then first available
                const preferredOrder = ['solid', 'regular', 'brands', 'light', 'thin', 'duotone'];

                for (const preferred of preferredOrder) {
                    const svgData = icon.svgs.find(s => s.familyStyle?.style === preferred);
                    if (svgData && svgData.pathData) {
                        return `<svg viewBox="0 0 512 512"><path d="${svgData.pathData}"/></svg>`;
                    }
                }

                // Fallback to first available
                if (icon.svgs[0] && icon.svgs[0].pathData) {
                    return `<svg viewBox="0 0 512 512"><path d="${icon.svgs[0].pathData}"/></svg>`;
                }

                return '<svg viewBox="0 0 24 24"><rect fill="#ccc" width="24" height="24" rx="4"/></svg>';
            },

            getIconStyle(icon) {
                if (!icon.familyStylesByLicense?.free || icon.familyStylesByLicense.free.length === 0) {
                    return 'solid';
                }

                return icon.familyStylesByLicense.free[0].style || 'solid';
            },

            saveIcon(icon) {
                const styleMap = {
                    solid: 'fa-solid',
                    regular: 'fa-regular',
                    light: 'fa-light',
                    thin: 'fa-thin',
                    brands: 'fa-brands',
                    duotone: 'fa-duotone',
                };

                const style = this.getIconStyle(icon);
                const fa6_prefix = styleMap[style] || 'fa-solid';

                this.value = fa6_prefix + " fa-" + icon.id;
                this.selectedSvg = this.getIconSvg(icon);

                this.handleConfirm();
            },

            handleClose() {
                this.$emit("close");
            },

            handleConfirm() {
                this.$emit("confirm", {
                    value: this.value,
                    svg: this.selectedSvg
                });
            },
        },

        watch: {
            "filter.type": {
                handler(val) {
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
    width: 24%;
    aspect-ratio: 1 / 1;
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
    0%, 100% {
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
