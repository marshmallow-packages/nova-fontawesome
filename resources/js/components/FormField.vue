<template>
    <DefaultField :field="field">
        <template #field>
            <div>
                <div v-if="value" class="display-icon mb-4">
                    <span
                        class="relative inline-flex rounded-md dark:bg-gray-900 items-center justify-center p-1 border border-gray"
                        style="width: 4rem; height: 4rem"
                    >
                        <button
                            type="button"
                            class="close-icon z-20 bg-gray-200 dark:bg-gray-700 dark:text-gray-200 hover:bg-red-300 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-md transition-all cursor-pointer"
                            @click="clear"
                            title="Clear icon"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 mx-auto"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                        <div
                            v-if="isLoading"
                            class="skeleton-box animate-pulse bg-gray-200 dark:bg-gray-700 rounded w-full h-full"
                        ></div>
                        <div
                            v-else-if="selectedIconSvg"
                            v-html="selectedIconSvg"
                            class="p-1 display-icon-svg fill-current text-gray-700 dark:text-gray-200"
                        ></div>
                        <i v-else :class="value + ' js-icon fa-2x fa-fw'"></i>
                    </span>
                </div>
                <input
                    :id="field.name"
                    type="hidden"
                    class="w-full form-control form-input form-input-bordered"
                    :class="errorClasses"
                    :placeholder="field.name"
                    v-model="value"
                />

                <Button
                    type="button"
                    dusk="open-modal-button"
                    state="default"
                    variant="solid"
                    :disabled="modalOpen"
                    :loading="modalOpen"
                    :label="addButtonText"
                    @click.prevent="openModal"
                />

                <GeneralModal
                    class="fontawesome-modal max-w-4xl"
                    v-if="modalOpen"
                    :field="field"
                    @confirm="confirmModal"
                    @close="closeModal"
                />
            </div>
        </template>
    </DefaultField>
</template>

<script>
    import { FormField, HandlesValidationErrors, Errors } from "laravel-nova";
    import { Button } from "laravel-nova-ui";
    import GeneralModal from "./GeneralModal.vue";
    import { iconMixin } from "../mixins/iconMixin.js";

    export default {
        mixins: [FormField, HandlesValidationErrors, iconMixin],
        props: ["resourceName", "resourceId", "field"],
        components: {
            Button,
            GeneralModal,
        },
        data: () => ({
            isLoading: false,
            icons: [],
            modalOpen: false,
            defaultIconObj: {},
            selectedIconData: null,
        }),
        computed: {
            pro() {
                return this.field.pro || false;
            },
            defaultIcon() {
                return this.field.default_icon || "";
            },
            defaultIconType() {
                return this.field.default_icon_type || "";
            },
            addButtonText() {
                return (
                    this.field.add_button_text ||
                    this.__("novaFontawesome.addIcon")
                );
            },
            enforceDefaultIcon() {
                return this.field.enforce_default_icon || false;
            },
            defaultIconOutput() {
                if (this.defaultIcon) {
                    return this.defaultIconType + " fa-" + this.defaultIcon;
                } else {
                    return "";
                }
            },
            selectedIconSvg() {
                if (this.selectedIconData && this.selectedIconData.svg) {
                    return this.selectedIconData.svg;
                }
                return null;
            },
        },
        watch: {
            value: {
                immediate: true,
                handler(newVal) {
                    if (newVal && !this.selectedIconData) {
                        this.fetchIconDetails(newVal);
                    }
                },
            },
        },
        mounted() {
            // Backwards compatibility: only sort if icons array exists
            if (this.icons && this.icons.length > 0) {
                this.icons.sort((a, b) =>
                    a.iconName > b.iconName
                        ? 1
                        : b.iconName > a.iconName
                          ? -1
                          : 0
                );
            }

            // Set default icon object
            if (this.defaultIcon && this.defaultIconType) {
                let i = this.icons.filter(
                    (icon) =>
                        icon.prefix === this.defaultIconType &&
                        icon.iconName === this.defaultIcon
                );

                if (i[0]) {
                    this.defaultIconObj = i[0];
                }
            }
        },
        methods: {
            async fetchIconDetails(iconClass) {
                try {
                    // Validate input
                    if (!iconClass || typeof iconClass !== "string") {
                        return;
                    }

                    // Parse the Font Awesome class string
                    const { faFamily, faStyle, faIcon } =
                        this.parseFontAwesomeClasses(iconClass);

                    // Validation: ensure name is not empty
                    if (!faIcon || faIcon.trim() === "") {
                        console.warn("Invalid icon name: empty or whitespace");
                        return;
                    }

                    // Resolve family and style with defaults
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
                        this.selectedIconData = this.getIconSvg(
                            data.icon,
                            family,
                            style
                        );
                    }
                } catch (error) {
                    // If 404, the icon doesn't exist in Font Awesome - silently ignore
                    if (error.response && error.response.status === 404) {
                        console.warn(
                            `Icon "${iconClass}" not found in Font Awesome API`
                        );
                        return;
                    }
                    console.error("Error fetching icon details:", error);
                } finally {
                    this.isLoading = false;
                }
            },

            openModal() {
                this.modalOpen = true;
            },

            confirmModal(iconData) {
                this.value = iconData.value;
                this.selectedIconData = iconData.svg
                    ? { svg: iconData.svg }
                    : null;
                this.modalOpen = false;
            },

            closeModal() {
                this.modalOpen = false;
            },

            /*
             * Set the initial, internal value for the field.
             */
            setInitialValue() {
                this.value = this.field.value || this.defaultIconOutput;
            },

            clear() {
                if (
                    this.enforceDefaultIcon &&
                    this.defaultIcon &&
                    this.defaultIconType &&
                    this.defaultIconObj.iconName
                ) {
                    this.value = this.defaultIconOutput;
                    this.saveIcon(this.defaultIconObj);
                } else {
                    this.value = "";
                    this.selectedIconData = null;
                }
            },

            /**
             * Fill the given FormData object with the field's internal value.
             */
            fill(formData) {
                formData.append(
                    this.field.attribute,
                    this.value || this.defaultIconOutput
                );
            },

            /**
             * Update the field's internal value.
             */
            handleChange(value) {
                this.value = value;
            },
        },
    };
</script>

<style>
    .fontawesome-modal .inner i {
        font-size: 3rem;
    }

    .display-icon i {
        font-size: 4rem;
    }

    .display-icon-svg {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 4rem;
        height: 4rem;
    }

    .display-icon-svg :deep(svg) {
        width: 100%;
        height: 100%;
        fill: currentColor;
    }

    .display-icon-svg svg {
        fill: currentColor;
    }

    .display-icon:hover .close-icon {
        display: block;
    }

    .close-icon {
        display: none;
        position: absolute;
        top: 0;
        right: 0;

        opacity: 0.75;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        transform: translate(50%, -50%);
    }

    .close-icon:hover {
        opacity: 1;
    }

    .svg-inline--fa.fa-w-20 {
        width: 2.5em;
    }

    .svg-inline--fa.fa-w-18 {
        width: 2.25em;
    }

    .svg-inline--fa.fa-w-16 {
        width: 2em;
    }

    .svg-inline--fa.fa-w-12 {
        width: 1.5em;
    }

    .fontawesome-inner {
        overflow-y: auto;
    }

    .icon-name {
        display: block;
        font-size: 0.75rem;
        margin-top: 0.5em;
        background: rgb(var(--colors-gray-100));
        padding: 0.25em 0.5em;
        border-radius: 0.25rem;
        color: rgb(var(--colors-gray-700));
    }

    .dark .icon-name {
        background: rgb(var(--colors-gray-700));
        color: rgb(var(--colors-gray-300));
    }

    .border-red {
        border-color: rgb(var(--colors-red-500));
    }

    .icon-box {
        width: 12%;
        aspect-ratio: 3 / 2;
        border: 1px solid rgb(var(--colors-gray-200));
        border-radius: 0.375rem;
        margin: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dark .icon-box {
        border-color: rgb(var(--colors-gray-700));
    }

    .icon-box:hover {
        border-color: rgb(var(--colors-primary-500));
        color: rgb(var(--colors-primary-500));
        background-color: rgb(var(--colors-primary-50));
    }

    .dark .icon-box:hover {
        background-color: rgb(var(--colors-primary-900) / 0.3);
    }

    .border-gray {
        border-color: rgb(var(--colors-gray-300));
    }

    .dark .border-gray {
        border-color: rgb(var(--colors-gray-700));
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
        .h-90p {
            height: 80%;
        }
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
