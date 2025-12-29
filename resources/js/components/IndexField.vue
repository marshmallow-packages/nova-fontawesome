<template>
    <span
        class="fontawesome-index-icon inline-flex rounded-md dark:bg-gray-900 items-center justify-center p-1 border border-gray"
        style="width: 2rem; height: 2rem"
    >
        <div
            v-if="isLoading"
            class="skeleton-box animate-pulse bg-gray-200 dark:bg-gray-700 rounded w-full h-full"
        ></div>
        <div
            v-else-if="iconSvg"
            v-html="iconSvg"
            class="index-icon-svg fill-current p-0.5 text-gray-700 dark:text-gray-200 w-full h-full"
        ></div>
        <i v-else :class="field.value"></i>
    </span>
</template>

<script>
    import { iconMixin } from "../mixins/iconMixin.js";

    export default {
        mixins: [iconMixin],
        props: ["resourceName", "field"],

        data: () => ({
            isLoading: false,
            iconSvg: null,
        }),

        mounted() {
            if (this.field.value) {
                this.fetchIconDetails(this.field.value);
            }
        },

        computed: {
            pro() {
                return this.field.pro || false;
            },
        },

        methods: {
            async fetchIconDetails(iconClass) {
                try {
                    if (!iconClass || typeof iconClass !== "string") {
                        return;
                    }

                    const { faFamily, faStyle, faIcon } =
                        this.parseFontAwesomeClasses(iconClass);

                    if (!faIcon || faIcon.trim() === "") {
                        return;
                    }

                    const { family, style } = this.getResolvedFamilyAndStyle(
                        faFamily,
                        faStyle
                    );

                    this.isLoading = true;

                    const params = {
                        family,
                        style,
                        version: this.field.version || "6.x",
                    };

                    const { data } = await Nova.request().get(
                        `/nova-vendor/nova-fontawesome/icon/${faIcon}`,
                        { params }
                    );

                    if (data.success && data.icon) {
                        const iconData = this.getIconSvg(
                            data.icon,
                            family,
                            style
                        );
                        this.iconSvg = iconData?.svg || null;
                    }
                } catch (error) {
                    if (error.response && error.response.status === 404) {
                        return;
                    }
                    console.error("Error fetching icon details:", error);
                } finally {
                    this.isLoading = false;
                }
            },
        },
    };
</script>

<style>
    .fontawesome-index-icon {
        font-size: 1.5em;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .index-icon-svg {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 0.75em;
        height: 0.75em;
    }

    .index-icon-svg :deep(svg) {
        width: 100%;
        height: 100%;
        fill: currentColor;
    }

    .index-icon-svg svg {
        fill: currentColor;
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
