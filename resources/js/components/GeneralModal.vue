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
                        ref="searchInput"
                    />
                </div>
            </div>

            <div
                class="fontawesome-inner overflow-y-auto"
                style="max-height: 60vh"
                @scroll="onScroll"
                ref="iconContainer"
            >
                <!-- Loading state -->
                <div v-if="isLoading && displayedIcons.length === 0">
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

                <!-- API Error state -->
                <div
                    v-else-if="apiError"
                    class="py-12 text-center"
                >
                    <div class="text-red-500 dark:text-red-400 mb-2">
                        <i class="fa-solid fa-triangle-exclamation text-4xl"></i>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 font-medium">
                        {{ apiErrorMessage }}
                    </p>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">
                        {{ __('novaFontawesome.tryAgainLater') }}
                    </p>
                </div>

                <!-- Empty state (before search) -->
                <div
                    v-else-if="!hasSearched && displayedIcons.length === 0"
                    class="py-12 text-center"
                >
                    <div class="text-gray-400 dark:text-gray-500 mb-4">
                        <i class="fa-solid fa-magnifying-glass text-4xl"></i>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 font-medium">
                        {{ __('novaFontawesome.searchPrompt') }}
                    </p>
                    <p class="text-gray-500 dark:text-gray-500 text-sm mt-1">
                        {{ __('novaFontawesome.searchHint') }}
                    </p>
                </div>

                <!-- Icons grid -->
                <div
                    class="flex flex-wrap items-stretch"
                    v-else-if="displayedIcons.length > 0"
                >
                    <div
                        v-for="(icon, index) in displayedIcons"
                        :key="icon._uniqueId || icon.id || index"
                        class="inner flex flex-col items-center justify-center text-center icon-box cursor-pointer transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                        @click="saveIcon(icon)"
                    >
                        <div class="icon-svg-container">
                            <i :class="getIconClass(icon)"></i>
                        </div>
                        <span class="icon-name">{{ icon.id }}</span>
                        <span class="icon-meta">
                            {{ getIconFamilyStyle(icon).family }} /
                            {{ getIconFamilyStyle(icon).style }}
                        </span>
                    </div>

                    <!-- Loading more indicator -->
                    <div
                        v-if="isLoadingMore"
                        class="w-full py-4 text-center"
                    >
                        <span class="text-gray-500 dark:text-gray-400">
                            {{ __('novaFontawesome.loadingMore') }}
                        </span>
                    </div>
                </div>

                <!-- No results after search -->
                <div
                    v-else-if="hasSearched && !isLoading && displayedIcons.length === 0"
                    class="py-12 text-center"
                >
                    <div class="text-gray-400 dark:text-gray-500 mb-4">
                        <i class="fa-solid fa-face-frown text-4xl"></i>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ __('novaFontawesome.noResults') }}
                    </p>
                </div>

                <!-- Search query too short -->
                <div
                    v-else-if="
                        filter.search.length > 0 &&
                        filter.search.length < minSearchLength
                    "
                    class="py-12 text-center"
                >
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ __('novaFontawesome.searchMinLength', { length: minSearchLength }) }}
                    </p>
                </div>
            </div>
        </ModalContent>

        <ModalFooter>
            <div class="ml-auto flex gap-3">
                <Button type="button" variant="ghost" @click="handleClose">
                    {{ __('novaFontawesome.cancel') }}
                </Button>

                <Button
                    type="button"
                    variant="solid"
                    :disabled="isLoading || !value"
                    :loading="isLoading"
                    @click="handleConfirm"
                >
                    {{ __('novaFontawesome.save') }}
                </Button>
            </div>
        </ModalFooter>
    </Modal>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova';
import { Button } from 'laravel-nova-ui';
import debounce from 'lodash/debounce';
import { fuzzySearchIcons } from '../utils/fuzzySearch.js';

export default {
    name: 'GeneralModal',
    mixins: [FormField, HandlesValidationErrors],
    components: {
        Button,
    },
    props: ['field'],
    data: () => ({
        isLoading: false,
        isLoadingMore: false,
        icons: [],
        displayedIcons: [],
        value: '',
        selectedSvg: null,
        filter: {
            family: 'all',
            style: 'all',
            search: '',
        },
        availableFamilies: [],
        availableStyles: [],
        debouncedSearch: null,
        hasSearched: false,
        apiError: false,
        apiErrorMessage: '',
        // Pagination
        cursor: null,
        hasMore: false,
        total: 0,
        // Filter counts
        filterCounts: {
            families: {},
            styles: {},
        },
    }),
    computed: {
        pro() {
            return this.field.pro || false;
        },
        minSearchLength() {
            return this.field.minSearchLength || 2;
        },
        fuzzySearchEnabled() {
            return this.field.fuzzySearch !== false;
        },
        fuzzySearchThreshold() {
            return this.field.fuzzySearchThreshold || 0.3;
        },
        familyOptions() {
            const totalCount = Object.values(this.filterCounts.families).reduce((a, b) => a + b, 0);
            const placeholder = {
                value: 'all',
                label: totalCount > 0
                    ? `${this.__('novaFontawesome.selectFamily.placeholder')} (${totalCount})`
                    : this.__('novaFontawesome.selectFamily.placeholder'),
            };
            return [
                placeholder,
                ...this.availableFamilies.map((f) => {
                    const count = this.filterCounts.families[f.id] || 0;
                    return {
                        value: f.id,
                        label: count > 0 ? `${f.label} (${count})` : f.label,
                    };
                }),
            ];
        },
        styleOptions() {
            const totalCount = Object.values(this.filterCounts.styles).reduce((a, b) => a + b, 0);
            const placeholder = {
                value: 'all',
                label: totalCount > 0
                    ? `${this.__('novaFontawesome.selectStyle.placeholder')} (${totalCount})`
                    : this.__('novaFontawesome.selectStyle.placeholder'),
            };
            return [
                placeholder,
                ...this.availableStyles.map((s) => {
                    const count = this.filterCounts.styles[s.id] || 0;
                    return {
                        value: s.id,
                        label: count > 0 ? `${s.label} (${count})` : s.label,
                    };
                }),
            ];
        },
    },
    created() {
        this.debouncedSearch = debounce(this.searchIcons, 300);
    },
    async mounted() {
        // Load metadata on mount
        await this.loadMetadata();
        // Focus search input
        this.$nextTick(() => {
            this.$refs.searchInput?.focus();
        });
    },
    methods: {
        async loadMetadata() {
            try {
                const params = {
                    version: this.field.version || '6.x',
                    freeOnly: this.field.freeOnly !== false,
                };

                const { data } = await Nova.request().get(
                    '/nova-vendor/nova-fontawesome/metadata',
                    { params }
                );

                if (data.metadata) {
                    this.availableFamilies = data.metadata.families || [];
                    this.availableStyles = data.metadata.styles || [];
                }
            } catch (error) {
                console.error('Error loading metadata:', error);
            }
        },

        async searchIcons() {
            if (this.filter.search.length < this.minSearchLength) {
                this.icons = [];
                this.displayedIcons = [];
                this.cursor = null;
                this.hasMore = false;
                this.total = 0;
                this.hasSearched = false;
                this.apiError = false;
                this.filterCounts = { families: {}, styles: {} };
                return;
            }

            this.isLoading = true;
            this.hasSearched = true;
            this.cursor = null;
            this.apiError = false;
            this.apiErrorMessage = '';

            try {
                const params = {
                    query: this.filter.search,
                    version: this.field.version || '6.x',
                    first: this.field.maxResults || 100,
                    freeOnly: this.field.freeOnly !== false,
                };

                const { data } = await Nova.request().get(
                    '/nova-vendor/nova-fontawesome/search',
                    { params }
                );

                // Check for API error
                if (data.error) {
                    this.apiError = true;
                    this.apiErrorMessage = data.message || this.__('novaFontawesome.apiUnavailable');
                    this.icons = [];
                    this.displayedIcons = [];
                    return;
                }

                // Store all icons
                this.icons = data.icons || [];
                this.cursor = data.cursor;
                this.hasMore = data.hasMore || false;
                this.total = data.total || this.icons.length;

                // Calculate filter counts
                this.calculateFilterCounts(this.icons);

                // Apply local filters
                this.applyFilters();
            } catch (error) {
                console.error('Error searching icons:', error);
                // Try fuzzy fallback
                this.useFuzzyFallback();
            } finally {
                this.isLoading = false;
            }
        },

        async loadMoreIcons() {
            if (!this.hasMore || this.isLoadingMore || !this.cursor) {
                return;
            }

            this.isLoadingMore = true;

            try {
                const params = {
                    query: this.filter.search,
                    version: this.field.version || '6.x',
                    first: this.field.maxResults || 100,
                    freeOnly: this.field.freeOnly !== false,
                    cursor: this.cursor,
                };

                const { data } = await Nova.request().get(
                    '/nova-vendor/nova-fontawesome/search',
                    { params }
                );

                if (data.error) {
                    return;
                }

                // Append new icons
                const newIcons = data.icons || [];
                this.icons = [...this.icons, ...newIcons];
                this.cursor = data.cursor;
                this.hasMore = data.hasMore || false;
                this.total = data.total || this.icons.length;

                // Recalculate filter counts
                this.calculateFilterCounts(this.icons);

                // Re-apply filters
                this.applyFilters();
            } catch (error) {
                console.error('Error loading more icons:', error);
            } finally {
                this.isLoadingMore = false;
            }
        },

        applyFilters() {
            let filteredIcons = this.icons;

            if (this.filter.family && this.filter.family !== 'all') {
                filteredIcons = filteredIcons.filter(icon => {
                    const iconFamily = icon._selectedStyle?.family || 'classic';
                    return iconFamily === this.filter.family;
                });
            }

            if (this.filter.style && this.filter.style !== 'all') {
                filteredIcons = filteredIcons.filter(icon => {
                    const iconStyle = icon._selectedStyle?.style || 'solid';
                    return iconStyle === this.filter.style;
                });
            }

            this.displayedIcons = filteredIcons;
        },

        calculateFilterCounts(icons) {
            const familyCounts = {};
            const styleCounts = {};

            icons.forEach(icon => {
                const family = icon._selectedStyle?.family || 'classic';
                const style = icon._selectedStyle?.style || 'solid';

                familyCounts[family] = (familyCounts[family] || 0) + 1;
                styleCounts[style] = (styleCounts[style] || 0) + 1;
            });

            this.filterCounts = {
                families: familyCounts,
                styles: styleCounts,
            };
        },

        useFuzzyFallback() {
            if (this.fuzzySearchEnabled && this.filter.search.length >= this.minSearchLength) {
                let fuzzyResults = fuzzySearchIcons(this.filter.search, {
                    threshold: this.fuzzySearchThreshold,
                    maxResults: this.field.maxResults || 50,
                });

                // Apply family/style filters to fuzzy results
                if (this.filter.family && this.filter.family !== 'all') {
                    fuzzyResults = fuzzyResults.filter((icon) => {
                        const styles = this.getAvailableStyles(icon);
                        return styles.some(
                            (s) => s.family === this.filter.family
                        );
                    });
                }

                if (this.filter.style && this.filter.style !== 'all') {
                    fuzzyResults = fuzzyResults.filter((icon) => {
                        const styles = this.getAvailableStyles(icon);
                        return styles.some(
                            (s) => s.style === this.filter.style
                        );
                    });
                }

                this.icons = fuzzyResults;
                this.displayedIcons = fuzzyResults;
                this.hasMore = false;
                this.cursor = null;
            } else {
                this.icons = [];
                this.displayedIcons = [];
            }
        },

        onScroll({ target: { scrollTop, clientHeight, scrollHeight } }) {
            // Load more when near bottom
            if (
                scrollTop + clientHeight >= scrollHeight - 250 &&
                this.hasMore &&
                !this.isLoadingMore
            ) {
                this.loadMoreIcons();
            }
        },

        getIconClass(icon) {
            // Build FA CSS class from icon data
            const { family, style } = this.getIconFamilyStyle(icon);

            // Handle different families
            if (family === 'brands') {
                return `fa-brands fa-${icon.id}`;
            } else if (family === 'sharp') {
                return `fa-sharp fa-${style} fa-${icon.id}`;
            } else if (family === 'sharp-duotone') {
                return `fa-sharp-duotone fa-${style} fa-${icon.id}`;
            } else if (family === 'duotone') {
                return `fa-duotone fa-${style} fa-${icon.id}`;
            }

            // Classic (default): just the style class
            return `fa-${style} fa-${icon.id}`;
        },

        getIconSvg(icon) {
            if (!icon.svgs || icon.svgs.length === 0) {
                return null;
            }

            // Prefer solid, then regular, then first available
            const preferredOrder = [
                'solid',
                'regular',
                'brands',
                'light',
                'thin',
                'duotone',
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
            const pathData = svgData.pathData;
            const width = svgData.width || 512;
            const height = svgData.height || 512;

            if (!pathData || pathData.length === 0) {
                return `<svg viewBox="0 0 ${width} ${height}"></svg>`;
            }

            // pathData is an array
            // For monotone: only one path (index 0)
            // For duotone: two paths - index 0 is secondary, index 1 is primary
            const isDuotone = style === 'duotone' && pathData.length === 2;

            let paths = '';
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

        /**
         * Get all available styles for an icon (both free and pro if freeOnly is false)
         */
        getAvailableStyles(icon) {
            const freeStyles = icon.familyStylesByLicense?.free || [];
            const proStyles = icon.familyStylesByLicense?.pro || [];

            // If freeOnly is false, include pro styles
            if (this.field.freeOnly === false) {
                return [...freeStyles, ...proStyles];
            }

            return freeStyles;
        },

        getIconFamilyStyle(icon) {
            // If the backend has pre-selected a style (expanded results), use it
            if (icon._selectedStyle) {
                return {
                    family: icon._selectedStyle.family || 'classic',
                    style: icon._selectedStyle.style || 'solid',
                };
            }

            const styles = this.getAvailableStyles(icon);

            if (styles.length === 0) {
                return { family: 'classic', style: 'solid' };
            }

            // Prefer the currently selected style/family filter if available
            if (this.filter.style !== 'all' || this.filter.family !== 'all') {
                const matchingStyle = styles.find(s => {
                    const styleMatch = this.filter.style === 'all' || s.style === this.filter.style;
                    const familyMatch = this.filter.family === 'all' || s.family === this.filter.family;
                    return styleMatch && familyMatch;
                });
                if (matchingStyle) {
                    return {
                        family: matchingStyle.family || 'classic',
                        style: matchingStyle.style || 'solid',
                    };
                }
            }

            const firstAvailable = styles[0];
            return {
                family: firstAvailable.family || 'classic',
                style: firstAvailable.style || 'solid',
            };
        },

        saveIcon(icon) {
            const { family, style } = this.getIconFamilyStyle(icon);

            // Build the CSS class string according to FontAwesome structure
            let classString = '';

            // Handle different families
            if (family === 'brands') {
                // Brands: fa-brands (both family and style)
                classString = 'fa-brands';
            } else if (family === 'sharp') {
                // Sharp: fa-sharp + style class
                classString = `fa-sharp fa-${style}`;
            } else if (family === 'sharp-duotone') {
                // Sharp Duotone: fa-sharp-duotone + style class
                classString = `fa-sharp-duotone fa-${style}`;
            } else if (family === 'duotone') {
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
            this.$emit('close');
        },

        handleConfirm() {
            this.$emit('confirm', {
                value: this.value,
                svg: this.selectedSvg,
            });
        },
    },

    watch: {
        'filter.family': {
            handler() {
                // Re-apply filters when family changes
                if (this.icons.length > 0) {
                    this.applyFilters();
                }
            },
        },
        'filter.style': {
            handler() {
                // Re-apply filters when style changes
                if (this.icons.length > 0) {
                    this.applyFilters();
                }
            },
        },
    },
};
</script>

<style scoped>
.icon-box {
    width: calc(16.666% - 0.5rem);
    aspect-ratio: 1 / 1;
    border: 1px solid rgb(var(--colors-gray-200));
    border-radius: 0.375rem;
    margin: 0.25rem;
    padding: 0.75rem;
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
    height: 40px;
    max-width: 100%;
    overflow: hidden;
}

.icon-svg-container :deep(svg) {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    fill: currentColor;
}

.icon-svg-container i {
    font-size: 1.75rem;
    max-width: 100%;
    text-align: center;
}

.icon-name {
    display: block;
    font-size: 0.7rem;
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

.icon-meta {
    display: block;
    font-size: 0.6rem;
    margin-top: 0.25rem;
    color: rgb(var(--colors-gray-500));
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

.dark .icon-name {
    background: rgb(var(--colors-gray-700));
    color: rgb(var(--colors-gray-300));
}

.dark .icon-meta {
    color: rgb(var(--colors-gray-400));
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
        width: calc(25% - 0.5rem);
    }
}

@media (max-width: 900px) {
    .icon-box {
        width: calc(50% - 0.5rem);
    }
}
</style>
