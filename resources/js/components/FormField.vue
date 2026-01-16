<template>
    <DefaultField :field="field">
        <template #field>
            <div>
                <div
                    v-if="value"
                    class="display-icon-wrapper mb-4 flex items-center gap-4"
                >
                    <div
                        class="display-icon relative inline-flex rounded-md dark:bg-gray-900 items-center justify-center p-1 border border-gray"
                        style="width: 4rem; height: 4rem"
                    >
                        <button
                            type="button"
                            class="close-icon z-20 bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-300 hover:bg-red-400 hover:text-white p-0.5 rounded-full shadow-sm transition-all cursor-pointer"
                            style="
                                width: 18px;
                                height: 18px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            "
                            @click="clear"
                            title="Clear icon"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="10"
                                height="10"
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
                        <i :class="value" style="font-size: 2rem"></i>
                    </div>
                    <div class="icon-info text-sm">
                        <div
                            class="font-medium text-gray-700 dark:text-gray-300"
                        >
                            {{ iconName }}
                        </div>
                        <div class="text-gray-500 dark:text-gray-400 text-xs">
                            {{ iconFamily }} / {{ iconStyle }}
                        </div>
                    </div>
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
    import { FormField, HandlesValidationErrors } from "laravel-nova";
    import { Button } from "laravel-nova-ui";
    import GeneralModal from "./GeneralModal.vue";

    export default {
        mixins: [FormField, HandlesValidationErrors],
        props: ["resourceName", "resourceId", "field"],
        components: {
            Button,
            GeneralModal,
        },
        data: () => ({
            modalOpen: false,
        }),
        computed: {
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
                }
                return "";
            },
            iconName() {
                if (!this.value) return "";
                // Extract icon name from class string (e.g., "fa-solid fa-house" -> "house")
                const match = this.value.match(/fa-([a-z0-9-]+)$/i);
                return match ? match[1] : this.value;
            },
            iconFamily() {
                if (!this.value) return "";
                // Determine family from class string
                if (this.value.includes("fa-brands")) return "brands";
                if (this.value.includes("fa-sharp-duotone"))
                    return "sharp-duotone";
                if (this.value.includes("fa-sharp")) return "sharp";
                if (this.value.includes("fa-duotone")) return "duotone";
                return "classic";
            },
            iconStyle() {
                if (!this.value) return "";
                // Determine style from class string
                if (this.value.includes("fa-brands")) return "brands";
                if (this.value.includes("fa-solid")) return "solid";
                if (this.value.includes("fa-regular")) return "regular";
                if (this.value.includes("fa-light")) return "light";
                if (this.value.includes("fa-thin")) return "thin";
                if (this.value.includes("fa-duotone")) return "duotone";
                return "solid";
            },
        },
        methods: {
            openModal() {
                this.modalOpen = true;
            },

            confirmModal(iconData) {
                this.value = iconData.value;
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
                if (this.enforceDefaultIcon && this.defaultIconOutput) {
                    this.value = this.defaultIconOutput;
                } else {
                    this.value = "";
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
        font-size: 1.75rem;
        max-width: 100%;
    }

    .display-icon i {
        font-size: 2rem;
        max-width: 100%;
        max-height: 100%;
    }

    .display-icon svg {
        width: 2rem;
        height: 2rem;
    }

    .display-icon-svg {
        display: flex;
        align-items: center;
        justify-content: center;
        max-width: 100%;
        max-height: 100%;
    }

    .display-icon-svg :deep(svg) {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
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

    .border-gray {
        border-color: rgb(var(--colors-gray-300));
    }

    .dark .border-gray {
        border-color: rgb(var(--colors-gray-700));
    }
</style>
