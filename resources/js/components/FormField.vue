<template>
    <DefaultField :field="field">
        <template #field>
            <div>
                <div v-if="value" class="display-icon mb-4">
                    <span class="relative inline-block p-8 border border-gray">
                        <i :class="value + ' js-icon'"></i>

                        <span class="close-icon" @click="clear">
                            <i class="fa fa-times-circle"></i>
                        </span>
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
                <button
                    class="flex-shrink-0 shadow rounded focus:outline-none focus:ring bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 inline-flex items-center font-bold px-4 h-9 text-sm flex-shrink-0"
                    @click.prevent="openModal"
                    v-text="addButtonText"
                ></button>

                <GeneralModal
                    class="fontawesome-modal max-w-3xl"
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
    import GeneralModal from "./GeneralModal.vue";

    export default {
        mixins: [FormField, HandlesValidationErrors],
        props: ["resourceName", "resourceId", "field"],
        components: {
            GeneralModal,
        },
        data: () => ({
            isLoading: false,
            icons: [],
            modalOpen: false,
            defaultIconObj: {},
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
                return this.field.add_button_text || "Add Icon";
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
        },
        mounted() {
            this.icons.sort((a, b) =>
                a.iconName > b.iconName ? 1 : b.iconName > a.iconName ? -1 : 0
            );

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
            openModal() {
                this.modalOpen = true;
            },
            confirmModal(iconData) {
                this.value = iconData;
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

    .close-icon i {
        font-size: 1.5rem !important;
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
        height: 90%;
        overflow: scroll;
    }

    .h-90p {
        height: 90%;
    }

    .fontawesome-close {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        right: 1.5rem;
        font-size: 1.5rem;
        color: #3c4655;
    }

    .icon-name {
        display: block;
        font-size: 12px;
        margin-top: 0.5em;
        background: #fafafa;
        padding: 0.2em;
    }

    .border-red {
        border-color: #ff123b;
    }

    .icon-box {
        width: 25%;
        outline: 1px solid #e0e0e0;
        outline-offset: -0.5rem;
    }

    .icon-box:hover {
        outline: 1px solid #ff123b;
        color: #ff123b;
    }

    .border-gray {
        border-color: #e0e0e0;
    }

    @media (max-width: 1279px) {
        .icon-box {
            width: 25%;
        }
    }

    @media (max-width: 900px) {
        .icon-box {
            width: 50%;
        }
        .h-90p {
            height: 80%;
        }
    }
</style>
