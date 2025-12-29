<template>
    <PanelItem :field="field">
        <template #value>
            <span
                class="fontawesome-detail-icon relative inline-flex rounded-md dark:bg-gray-900 items-center justify-center p-1 border border-gray"
                style="width: 2em; height: 2em"
            >
                <div
                    v-if="isLoading"
                    class="skeleton-box animate-pulse bg-gray-200 dark:bg-gray-700 rounded w-full h-full"
                ></div>
                <div
                    v-else-if="iconSvg"
                    v-html="iconSvg"
                    class="detail-icon-svg p-1 display-icon-svg fill-current text-gray-700 dark:text-gray-200"
                ></div>
                <i v-else :class="field.value"></i>
            </span>
        </template>
    </PanelItem>
</template>

<script>
    import { iconMixin } from "../mixins/iconMixin.js";

    export default {
        mixins: [iconMixin],
        props: ["resource", "resourceName", "resourceId", "field"],

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
    .fontawesome-detail-icon {
        font-size: 2em;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .detail-icon-svg {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2em;
        height: 2em;
    }

    .detail-icon i {
        font-size: 4rem;
    }

    .detail-icon-svg :deep(svg) {
        width: 100%;
        height: 100%;
        fill: currentColor;
    }

    .detail-icon-svg svg {
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
