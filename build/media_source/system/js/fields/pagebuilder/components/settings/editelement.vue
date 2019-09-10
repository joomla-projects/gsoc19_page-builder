<template>
	<div>
		<!-- Settings for editing elements -->
		<div>
			<legend> {{ translate('JLIB_PAGEBUILDER_EDIT') }}{{ elementSelected.type }}</legend>
			<div class="form-group">
				<label for="element_class">{{ translate('JLIB_PAGEBUILDER_ADD_CLASS') }}</label>
				<div>
					<input type="text" name="element_class" id="element_class" class="class_input"
						   :placeholder="translate('JLIB_PAGEBUILDER_NONE')" v-model="element_class">
					<span class="fa fa-plus add_class_button hoverCursor" @click="add"
						  title="Add" aria-hidden="true"></span>

					<div id="color_picker_container" v-if="elementSelected.type === 'column'">
						<div id="backgroundcolor_input_area">
							<label>{{ translate('JLIB_PAGEBUILDER_BACKGROUND_COLOR') }}</label>
							<input type="text" name="background_color" id="backgroundcolor_input"
								   class="backgroundcolor_input"
								   v-model="backgroundcolor_converter">
							<div id="backgroundcolor_display" class="backgroundcolor_display hoverCursor"
								 @click="open_backgroundcolor_picker"
								 :style="{'background-color': element_style.backgroundcolor}"
								 title="backgroundcolor_display" aria-hidden="true"></div>
							<div v-if="backgroundcolor_picker" class="background_picker_container">
								<verte display="widget" v-model="element_style.backgroundcolor"
									   id="backgroundcolor_picker"></verte>
								<button type="button" class="btn btn-secondary background_reset_button"
										@click="reset_backgroundcolor">{{ translate('JLIB_PAGEBUILDER_RESET') }}
								</button>
								<button type="button" class="btn btn-secondary background_close_button"
										@click="close_backgroundcolor_picker">{{ translate('JLIB_PAGEBUILDER_CLOSE') }}
								</button>
							</div>
						</div>

						<div id="fontcolor_input_area">
							<label>{{ translate('JLIB_PAGEBUILDER_FONT_COLOR') }}</label>
							<input type="text" name="fontcolor_color" id="fontcolor_input"
								   class="fontcolor_input" @click="open_fontcolor_picker"
								   v-model="fontcolor_converter">
							<div id="fontcolor_display" class="fontcolor_display hoverCursor"
								 @click="open_fontcolor_picker"
								 title="fontcolor_display" aria-hidden="true"
								 :style="{'background-color': element_style.fontcolor}"></div>
							<div v-if="fontcolor_picker" class="fontcolor_picker_container">
								<verte display="widget" v-model="element_style.fontcolor"
									   id="fontcolor_picker"></verte>
								<button type="button" class="btn btn-secondary fontcolor_reset_button"
										@click="reset_fontcolor">{{ translate('JLIB_PAGEBUILDER_RESET') }}
								</button>
								<button type="button" class="btn btn-secondary fontcolor_close_button"
										@click="close_fontcolor_picker">{{ translate('JLIB_PAGEBUILDER_CLOSE') }}
								</button>
							</div>
						</div>

						<div id="linkcolor_input_area">
							<label>{{ translate('JLIB_PAGEBUILDER_LINK_COLOR') }}</label>
							<input type="text" name="linkcolor_color" id="linkcolor_input"
								   class="linkcolor_input" @click="open_linkcolor_picker"
								   v-model="linkcolor_converter">
							<div id="linkcolor_display" class="linkcolor_display hoverCursor"
								 @click="open_linkcolor_picker"
								 title="linkcolor_display" aria-hidden="true"
								 :style="{'background-color': element_style.linkcolor}"></div>
							<div v-if="linkcolor_picker" class="linkcolor_picker_container">
								<verte display="widget" v-model="element_style.linkcolor"
									   id="linkcolor_picker"></verte>
								<button type="button" class="btn btn-secondary linkcolor_reset_button"
										@click="reset_linkcolor">{{ translate('JLIB_PAGEBUILDER_RESET') }}
								</button>
								<button type="button" class="btn btn-secondary linkcolor_close_button"
										@click="close_linkcolor_picker">{{ translate('JLIB_PAGEBUILDER_CLOSE') }}
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<div v-if="elementSelected.type === 'column'">
					<label for="element_offset">{{ translate('JLIB_PAGEBUILDER_ADD_OFFSET') }}</label>
					<br>
					<table class="table">
						<thead>
						<tr>
							<th scope="col">{{ translate('JLIB_PAGEBUILDER_DEVICE') }}</th>
							<th scope="col">{{ translate('JLIB_PAGEBUILDER_OFFSET') }}</th>
						</tr>
						</thead>
						<tbody>
						<tr v-for="size in offset_sizes">
							<th scope="row"><i :class="['fas', 'fa-' + size.icon, 'fa-lg', 'fa-rotate-' + size.rotate]"
											   class="icons_pictures"></i></th>
							<td class="control-group">
								<select name="element_offset" id="element_offset" class="custom-select custom-select-sm"
										v-model="element_offset[size.label]">
									<option v-for="opt in offset" v-if="opt.value <= threshold" :value="opt.value">
										{{opt.label}}
									</option>
								</select>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div v-for="(config, id) in configs" class="form-group">
					<label :for="id">{{ config.label }}</label>
					<select v-if="config.type === 'select'" :id="id" :name="id" class="custom-select"
							:required="config.required" v-model="elementSelected.options[id]">
						<option v-for="(value, key) in config.value" :value="value">{{ key }}</option>
					</select>
					<input v-else :id="id" @name="id" :type="config.type" class="form-control"
						   :required="config.required" v-model="elementSelected.options[id]"/>
				</div>
			</div>

			<div class="buttonsOnBottom">
				<button type="button" class="btn btn-secondary" @click="closeNav">{{ translate('JTOOLBAR_CLOSE') }}
				</button>
			</div>
		</div>
	</div>
</template>

<script>
    import {mapState, mapMutations} from 'vuex';

    export default {
        name: 'edit-element',
        computed: {
            ...mapState([
                'activeDevice',
                'elementSelected',
                'elements',
                'parent',
                'size'
            ]),

            backgroundcolor_converter: {
                get: function () {
                    let hslString = this.element_style.backgroundcolor;
                    let splitString = hslString.split(",");
                    let numbersFilter = splitString.map(this.filter_number);

                    let hexValue = this.hslToHex(numbersFilter[0], numbersFilter[1], numbersFilter[2]);
                    if (hexValue === '#NaNNaNNaN') {
                        hexValue = '';
                    }
                    this.modifyStyle({'background-color': hexValue});
                    return hexValue;
                },
                set: function (data) {
                    let testData = /(^#[0-9A-F]{6}$)|(^#[0-9A-F]{3}$)/i.test(data);

                    if (testData) {
                        this.element_style.backgroundcolor = this.hexToHSL(data);
                        console.log(this.element_style.backgroundcolor);
                    } else {
                        console.log('invalid');
                    }
                }
            },
            fontcolor_converter() {
                let hslString = this.element_style.fontcolor;
                let splitString = hslString.split(",");
                let numbersFilter = splitString.map(this.filter_number);

                let hexValue = this.hslToHex(numbersFilter[0], numbersFilter[1], numbersFilter[2]);
                if (hexValue === '#NaNNaNNaN') {
                    hexValue = '';
                }
                this.modifyStyle({'font-color': hexValue});
                return hexValue;
            },
            linkcolor_converter() {
                let hslString = this.element_style.linkcolor;
                let splitString = hslString.split(",");
                let numbersFilter = splitString.map(this.filter_number);

                let hexValue = this.hslToHex(numbersFilter[0], numbersFilter[1], numbersFilter[2]);
                if (hexValue === '#NaNNaNNaN') {
                    hexValue = '';
                }
                this.modifyStyle({'link-color': hexValue});
                return hexValue;
            },
            threshold() {
                return this.size - this.elementSelected.options.size[this.activeDevice];
            },
            configs() {
                return this.elements.find(el => el.id === this.elementSelected.type).config;
            },
        },
        mounted() {
            if (this.elementSelected.type === 'column') {
                this.element_style['backgroundcolor'] = this.hexToHSL(this.elementSelected.style[0]['background-color']);
                this.element_style['fontcolor'] = this.hexToHSL(this.elementSelected.style[1]['font-color']);
                this.element_style['linkcolor'] = this.hexToHSL(this.elementSelected.style[2]['link-color']);
            }

            this.element_class = this.elementSelected.options.class;
            if (this.elementSelected.options.offset)
                this.element_offset = this.elementSelected.options.offset;
        },
        watch: {
            elementSelected: {
                deep: true,
                handler() {
                    this.element_class = this.elementSelected.options.class;
                    if (this.elementSelected.options.offset)
                        this.element_offset = this.elementSelected.options.offset;
                },
            }
        },
        data() {
            return {
                backgroundcolor_picker: false,
                fontcolor_picker: false,
                linkcolor_picker: false,
                element_class: {
                    name: '',
                },
                element_style: {
                    backgroundcolor: 'hsl(0,0%,0%)',
                    fontcolor: 'hsl(0,0%,0%)',
                    linkcolor: 'hsl(0,0%,0%)',
                },
                element_offset: {},
                offset_sizes: [
                    {
                        icon: 'mobile-alt',
                        label: 'xs',
                        rotate: '0'
                    },
                    {
                        icon: 'tablet-alt',
                        label: 'sm',
                        rotate: '0'
                    },
                    {
                        icon: 'tablet-alt',
                        label: 'md',
                        rotate: '270'
                    },
                    {
                        icon: 'laptop',
                        label: 'lg',
                        rotate: '0'
                    },
                    {
                        icon: 'desktop',
                        label: 'xl',
                        rotate: '0'
                    },
                ],
                offset: [
                    {
                        value: 0,
                        label: 'No offset'
                    },
                    {
                        value: 1,
                        label: '1 column - 1/12'
                    },
                    {
                        value: 2,
                        label: '2 columns - 1/6'
                    },
                    {
                        value: 3,
                        label: '3 columns - 1/4'
                    },
                    {
                        value: 4,
                        label: '4 columns - 1/3'
                    },
                    {
                        value: 5,
                        label: '5 columns - 5/12'
                    },
                    {
                        value: 6,
                        label: '6 columns - 1/2'
                    },
                    {
                        value: 7,
                        label: '7 columns - 7/12'
                    },
                    {
                        value: 8,
                        label: '8 columns - 2/3'
                    },
                    {
                        value: 9,
                        label: '9 columns - 3/4'
                    },
                    {
                        value: 10,
                        label: '10 columns - 5/6'
                    },
                    {
                        value: 11,
                        label: '11 columns - 11/12'
                    },
                    {
                        value: 12,
                        label: '12 columns - 1/1'
                    }
                ]
            }
        },
        methods: {
            ...mapMutations([
                'closeNav',
                'modifyElement',
                'modifyStyle'
            ]),
            add() {
                let modify = {};
                modify.class = (this.element_class !== '') ? this.element_class : '';
                modify.offset = this.element_offset;
                this.modifyElement(modify);
            },
            open_backgroundcolor_picker() {
                this.backgroundcolor_picker = !this.backgroundcolor_picker;
            },
            reset_backgroundcolor() {
                this.element_style.backgroundcolor = "hsl(0,0%,0%)";
            },
            close_backgroundcolor_picker() {
                this.backgroundcolor_picker = false;
            },
            open_fontcolor_picker() {
                this.fontcolor_picker = !this.fontcolor_picker;
            },
            reset_fontcolor() {
                this.element_style.fontcolor = "hsl(0,0%,0%)";
            },
            close_fontcolor_picker() {
                this.fontcolor_picker = false;
            },
            open_linkcolor_picker() {
                this.linkcolor_picker = !this.linkcolor_picker;
            },
            reset_linkcolor() {
                this.element_style.linkcolor = "hsl(0,0%,0%)";
            },
            close_linkcolor_picker() {
                this.linkcolor_picker = false;
            },
            filter_number(word) {
                return parseFloat(word.split("").filter(c => c.match(/\d|\./)).join(""));
            },
            hexToHSL(H) {
                // Convert hex to RGB first
                let r = 0, g = 0, b = 0;
                if (H.length == 4) {
                    r = "0x" + H[1] + H[1];
                    g = "0x" + H[2] + H[2];
                    b = "0x" + H[3] + H[3];
                } else if (H.length == 7) {
                    r = "0x" + H[1] + H[2];
                    g = "0x" + H[3] + H[4];
                    b = "0x" + H[5] + H[6];
                }
                // Then to HSL
                r /= 255;
                g /= 255;
                b /= 255;
                let cmin = Math.min(r, g, b),
                    cmax = Math.max(r, g, b),
                    delta = cmax - cmin,
                    h = 0,
                    s = 0,
                    l = 0;

                if (delta == 0)
                    h = 0;
                else if (cmax == r)
                    h = ((g - b) / delta) % 6;
                else if (cmax == g)
                    h = (b - r) / delta + 2;
                else
                    h = (r - g) / delta + 4;

                h = Math.round(h * 60);

                if (h < 0)
                    h += 360;

                l = (cmax + cmin) / 2;
                s = delta == 0 ? 0 : delta / (1 - Math.abs(2 * l - 1));
                s = +(s * 100).toFixed(1);
                l = +(l * 100).toFixed(1);

                return "hsl(" + h + "," + s + "%," + l + "%)";
            },
            hslStringToHex(hslString) {
                let splitString = hslString.split(",");
                let numbersFilter = splitString.map(this.filter_number);

                let hexValue = this.hslToHex(numbersFilter[0], numbersFilter[1], numbersFilter[2]);
                if (hexValue === '#NaNNaNNaN') {
                    hexValue = '';
                }
                this.modifyStyle({'background-color': hexValue});
                return hexValue;
            },
            hslToHex(h, s, l) {
                h /= 360;
                s /= 100;
                l /= 100;
                let r, g, b;
                if (s === 0) {
                    r = g = b = l; // achromatic
                } else {
                    const hue2rgb = (p, q, t) => {
                        if (t < 0) t += 1;
                        if (t > 1) t -= 1;
                        if (t < 1 / 6) return p + (q - p) * 6 * t;
                        if (t < 1 / 2) return q;
                        if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
                        return p;
                    };
                    const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                    const p = 2 * l - q;
                    r = hue2rgb(p, q, h + 1 / 3);
                    g = hue2rgb(p, q, h);
                    b = hue2rgb(p, q, h - 1 / 3);
                }
                const toHex = x => {
                    const hex = Math.round(x * 255).toString(16);
                    return hex.length === 1 ? '0' + hex : hex;
                };
                return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
            },
        },
    }
</script>


<style lang="scss">

	.background_close_button {
		width: 45%;
	}

	.background_reset_button {
		width: 45%;
	}

	.fontcolor_close_button {
		width: 45%;
	}

	.fontcolor_reset_button {
		width: 45%;
	}

	.linkcolor_close_button {
		width: 45%;
	}

	.linkcolor_reset_button {
		width: 45%;
	}

	.fa-rotate-270 {
		margin-top: 0px;
	}

	.icons_pictures {
		margin-top: 15px;
	}

	.buttonsOnBottom {
		float: right;
	}

	.add_class_div {
		display: inline;
	}

	.class_input {
		padding: 5px;
	}

	.backgroundcolor_input {
		padding: 5px;
	}

	.fontcolor_input {
		padding: 5px;
	}

	.linkcolor_input {
		padding: 5px;
	}

	.add_class_button {
		margin-left: 15px;
	}

	.hoverCursor:hover {
		cursor: pointer;
	}

	.backgroundcolor_display {
		width: 28px;
		height: 28px;
		float: right;
		margin-top: 3px;
		margin-right: 20px;
		padding: 3px;
	}

	.fontcolor_display {
		width: 28px;
		height: 28px;
		float: right;
		margin-top: 3px;
		margin-right: 20px;
		padding: 3px;
	}

	.linkcolor_display {
		width: 28px;
		height: 28px;
		float: right;
		margin-top: 3px;
		margin-right: 20px;
		padding: 3px;
	}

</style>
