<template>
    <DefaultField :field="field">
        <template #field>
            <div class="fa-icon-field">
                <div
                    v-if="value"
                    class="icon-display-wrapper"
                >
                    <div class="icon-display-box">
                        <button
                            type="button"
                            class="close-button"
                            @click="clear"
                            title="Clear icon"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
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
                        <i :key="value" :class="value" class="icon-preview"></i>
                    </div>
                    <div class="icon-info">
                        <div class="icon-info-name">
                            {{ iconName }}
                        </div>
                        <div class="icon-info-meta">
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
                    v-if="modalOpen"
                    class="fontawesome-modal max-w-4xl"
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
        data() {
            return {
                modalOpen: false,
            };
        },
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
                console.log('confirmModal received:', iconData);
                console.log('Current value before:', this.value);
                this.value = iconData.value;
                console.log('Value after assignment:', this.value);
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
                const valueToSave = this.value || this.defaultIconOutput;
                console.log('FontAwesome fill:', this.field.attribute, valueToSave);
                formData.append(this.field.attribute, valueToSave);
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
    .fa-icon-field .icon-display-wrapper {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .fa-icon-field .icon-display-box {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 4rem;
        height: 4rem;
        padding: 0.25rem;
        border-radius: 0.375rem;
        border: 1px solid rgb(var(--colors-gray-300));
        overflow: visible;
    }

    .dark .fa-icon-field .icon-display-box {
        background-color: rgb(var(--colors-gray-900));
        border-color: rgb(var(--colors-gray-700));
    }

    .fa-icon-field .icon-preview {
        font-size: 2rem;
        pointer-events: none;
    }

    .fa-icon-field .close-button {
        position: absolute;
        top: 0;
        right: 0;
        z-index: 20;
        width: 18px;
        height: 18px;
        padding: 3px;
        transform: translate(50%, -50%);
        background-color: rgb(var(--colors-gray-300));
        color: rgb(var(--colors-gray-600));
        border-radius: 9999px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.15s ease, visibility 0.15s ease, background-color 0.15s ease, color 0.15s ease;
        border: none;
    }

    .fa-icon-field .close-button svg {
        width: 100%;
        height: 100%;
    }

    .dark .fa-icon-field .close-button {
        background-color: rgb(var(--colors-gray-600));
        color: rgb(var(--colors-gray-300));
    }

    .fa-icon-field .icon-display-box:hover .close-button {
        opacity: 1;
        visibility: visible;
    }

    .fa-icon-field .close-button:hover {
        background-color: rgb(var(--colors-red-500));
        color: white;
    }

    .fa-icon-field .icon-info {
        font-size: 0.875rem;
    }

    .fa-icon-field .icon-info-name {
        font-weight: 500;
        color: rgb(var(--colors-gray-700));
    }

    .dark .fa-icon-field .icon-info-name {
        color: rgb(var(--colors-gray-300));
    }

    .fa-icon-field .icon-info-meta {
        font-size: 0.75rem;
        color: rgb(var(--colors-gray-500));
    }

    .dark .fa-icon-field .icon-info-meta {
        color: rgb(var(--colors-gray-400));
    }
</style>

<style>
    .fontawesome-modal .inner i {
        font-size: 1.75rem;
        max-width: 100%;
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
</style>
