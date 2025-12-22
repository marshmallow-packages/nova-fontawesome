<template>
    <DefaultField :field="field">
        <template #field>
            <div>
                <div v-if="value" class="display-icon mb-4">
                    <span class="relative inline-flex items-center justify-center p-3 border border-gray" style="width: 80px; height: 80px;">
                        <div v-if="selectedIconSvg" v-html="selectedIconSvg" class="display-icon-svg"></div>
                        <i v-else :class="value + ' js-icon fa-2x fa-fw'"></i>

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
    import { Button } from 'laravel-nova-ui';
    import GeneralModal from "./GeneralModal.vue";

    export default {
        mixins: [FormField, HandlesValidationErrors],
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
                    a.iconName > b.iconName ? 1 : b.iconName > a.iconName ? -1 : 0
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
                    if (!iconClass || typeof iconClass !== 'string') {
                        return;
                    }

                    // Extract icon name from class string (e.g., "fa-solid fa-user" -> "user")
                    const parts = iconClass.trim().split(' ');

                    // Check if this looks like a valid Font Awesome class string
                    // Must have at least one style prefix (fa-solid, fa-regular, etc.)
                    const hasStylePrefix = parts.some(p => ['fa-solid', 'fa-regular', 'fa-light', 'fa-thin', 'fa-brands', 'fa-duotone'].includes(p));

                    if (!hasStylePrefix) {
                        // Invalid format - missing style prefix
                        console.warn('Invalid icon format (missing style prefix):', iconClass);
                        return;
                    }

                    const iconName = parts.find(p => p.startsWith('fa-') && !['fa-solid', 'fa-regular', 'fa-light', 'fa-thin', 'fa-brands', 'fa-duotone'].includes(p));

                    if (!iconName) {
                        // No icon name found after style prefix
                        console.warn('Invalid icon format (no icon name found):', iconClass);
                        return;
                    }

                    const name = iconName.replace('fa-', '');

                    // Validation: ensure name is not empty
                    if (!name || name.trim() === '') {
                        console.warn('Invalid icon name: empty or whitespace');
                        return;
                    }

                    const params = {
                        version: this.field.version || '6.x',
                    };

                    const { data } = await Nova.request().get(`/nova-vendor/nova-fontawesome/icon/${name}`, { params });

                    if (data.success && data.icon) {
                        this.selectedIconData = this.getIconSvg(data.icon);
                    }
                } catch (error) {
                    // If 404, the icon doesn't exist in Font Awesome - silently ignore
                    if (error.response && error.response.status === 404) {
                        console.warn(`Icon "${iconClass}" not found in Font Awesome API`);
                        return;
                    }
                    console.error('Error fetching icon details:', error);
                }
            },
            getIconSvg(icon) {
                if (!icon.svgs || icon.svgs.length === 0) {
                    return null;
                }

                // Prefer solid, then regular, then first available
                const preferredOrder = ['solid', 'regular', 'brands', 'light', 'thin', 'duotone'];

                for (const preferred of preferredOrder) {
                    const svgData = icon.svgs.find(s => s.familyStyle?.style === preferred);
                    if (svgData && svgData.pathData) {
                        const svg = `<svg viewBox="0 0 512 512"><path d="${svgData.pathData}"/></svg>`;
                        return { svg, icon };
                    }
                }

                // Fallback to first available
                if (icon.svgs[0] && icon.svgs[0].pathData) {
                    const svg = `<svg viewBox="0 0 512 512"><path d="${icon.svgs[0].pathData}"/></svg>`;
                    return { svg, icon };
                }

                return null;
            },
            openModal() {
                this.modalOpen = true;
            },
            confirmModal(iconData) {
                this.value = iconData.value;
                this.selectedIconData = iconData.svg ? { svg: iconData.svg } : null;
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
        fill: rgb(var(--colors-gray-700));
    }

    .display-icon-svg :deep(svg) {
        width: 100%;
        height: 100%;
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
        width: 24%;
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
</style>
