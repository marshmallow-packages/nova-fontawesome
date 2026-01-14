<template>
    <div class="icon-renderer" :class="sizeClass">
        <!-- Loading state -->
        <div
            v-if="loading"
            class="skeleton-box animate-pulse bg-gray-200 dark:bg-gray-700 rounded"
            :class="skeletonSizeClass"
        ></div>

        <!-- SVG rendering (preferred) -->
        <div
            v-else-if="svgContent"
            v-html="svgContent"
            class="icon-svg fill-current"
            :class="colorClass"
        ></div>

        <!-- Fallback to CSS class -->
        <i
            v-else-if="iconClass"
            :class="[iconClass, 'fa-fw', sizeIconClass, colorClass]"
        ></i>

        <!-- Empty state -->
        <div v-else class="icon-placeholder" :class="sizeClass">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 512 512"
                class="fill-current text-gray-300 dark:text-gray-600"
            >
                <path
                    d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm169.8-90.7c7.9-22.3 29.1-37.3 52.8-37.3l58.3 0c34.9 0 63.1 28.3 63.1 63.1c0 22.6-12.1 43.5-31.7 54.8L280 264.4c-.2 13-10.9 23.6-24 23.6c-13.3 0-24-10.7-24-24l0-13.5c0-8.6 4.6-16.5 12.1-20.8l44.3-25.4c4.7-2.7 7.6-7.7 7.6-13.1c0-8.4-6.8-15.1-15.1-15.1l-58.3 0c-3.4 0-6.4 2.1-7.5 5.3l-.4 1.2c-4.4 12.5-18.2 19-30.6 14.6s-19-18.2-14.6-30.6l.4-1.2zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"
                />
            </svg>
        </div>
    </div>
</template>

<script>
export default {
    name: "IconRenderer",
    props: {
        // The Font Awesome class string (e.g., "fa-solid fa-user")
        iconClass: {
            type: String,
            default: "",
        },
        // Pre-built SVG content
        svg: {
            type: String,
            default: null,
        },
        // Icon data object from API
        iconData: {
            type: Object,
            default: null,
        },
        // Size: xs, sm, md, lg, xl, 2xl
        size: {
            type: String,
            default: "md",
            validator: (value) =>
                ["xs", "sm", "md", "lg", "xl", "2xl"].includes(value),
        },
        // Custom color class
        color: {
            type: String,
            default: "",
        },
        // Loading state
        loading: {
            type: Boolean,
            default: false,
        },
        // Preferred family for SVG selection
        preferredFamily: {
            type: String,
            default: null,
        },
        // Preferred style for SVG selection
        preferredStyle: {
            type: String,
            default: null,
        },
    },
    computed: {
        svgContent() {
            // Use provided SVG first
            if (this.svg) {
                return this.svg;
            }

            // Build SVG from iconData if available
            if (this.iconData && this.iconData.svgs) {
                return this.buildSvgFromIconData(this.iconData);
            }

            return null;
        },
        sizeClass() {
            const sizes = {
                xs: "w-4 h-4",
                sm: "w-6 h-6",
                md: "w-8 h-8",
                lg: "w-12 h-12",
                xl: "w-16 h-16",
                "2xl": "w-24 h-24",
            };
            return sizes[this.size] || sizes.md;
        },
        skeletonSizeClass() {
            return this.sizeClass;
        },
        sizeIconClass() {
            const sizes = {
                xs: "fa-sm",
                sm: "fa-lg",
                md: "fa-xl",
                lg: "fa-2x",
                xl: "fa-3x",
                "2xl": "fa-4x",
            };
            return sizes[this.size] || sizes.md;
        },
        colorClass() {
            if (this.color) {
                return this.color;
            }
            return "text-gray-700 dark:text-gray-200";
        },
    },
    methods: {
        buildSvgFromIconData(iconData) {
            if (!iconData.svgs || iconData.svgs.length === 0) {
                return null;
            }

            // Try to find preferred family/style first
            let svgData = null;
            if (this.preferredFamily && this.preferredStyle) {
                svgData = iconData.svgs.find(
                    (s) =>
                        s.familyStyle?.family?.toLowerCase() ===
                            this.preferredFamily.toLowerCase() &&
                        s.familyStyle?.style?.toLowerCase() ===
                            this.preferredStyle.toLowerCase()
                );
            }

            // Fallback: prefer solid, then regular, then first available
            if (!svgData) {
                const preferredOrder = [
                    "solid",
                    "regular",
                    "brands",
                    "light",
                    "thin",
                    "duotone",
                ];

                for (const preferred of preferredOrder) {
                    svgData = iconData.svgs.find(
                        (s) =>
                            s.familyStyle?.style?.toLowerCase() === preferred
                    );
                    if (svgData && svgData.pathData) {
                        break;
                    }
                }
            }

            // Final fallback to first available
            if (!svgData && iconData.svgs[0]) {
                svgData = iconData.svgs[0];
            }

            if (!svgData || !svgData.pathData) {
                return null;
            }

            return this.buildSvgFromPath(svgData);
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

            // For duotone: two paths - index 0 is secondary, index 1 is primary
            const isDuotone =
                (family?.toLowerCase() === "duotone" ||
                    family?.toLowerCase() === "sharp-duotone" ||
                    style?.toLowerCase() === "duotone") &&
                pathData.length === 2;

            let paths = "";
            if (isDuotone) {
                if (pathData[0]) {
                    paths += `<path d="${pathData[0]}" opacity="0.4"/>`;
                }
                if (pathData[1]) {
                    paths += `<path d="${pathData[1]}"/>`;
                }
            } else {
                if (pathData[0]) {
                    paths = `<path d="${pathData[0]}"/>`;
                }
            }

            return `<svg viewBox="0 0 ${width} ${height}" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">${paths}</svg>`;
        },
    },
};
</script>

<style scoped>
.icon-renderer {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.icon-svg {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.icon-svg :deep(svg) {
    width: 100%;
    height: 100%;
    fill: currentColor;
}

.icon-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-placeholder svg {
    width: 50%;
    height: 50%;
}

.skeleton-box {
    display: inline-block;
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
</style>
