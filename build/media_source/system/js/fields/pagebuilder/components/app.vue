<template>
	<div class="pagebuilder_container">
		<div id="sidebar" class="sidebar">
			<div class="sidebar-content">
				<h2>{{ translate('JLIB_PAGEBUILDER_SETTINGS') }}

					<button type="button" class="btn btn-lg closeButton" @click="closeNav()" aria-label="Close">
						<span class="icon-cancel"></span>
					</button>
				</h2>


				<component :is="selectedSettings"></component>
			</div>
		</div>

		<div class="topbar row">
			<div class="col-8">
				<devices></devices>
			</div>
			<div class="col">
				<ul class="nav nav-pills">
					<li class="nav-item" id="placeholder_component">
						<button type="button" class="nav-link drag" id="drag_component" draggable="true"
								@dragstart="drag($event)">
							<i class="fas fa-file-alt"></i>
							<span>{{ translate('JLIB_PAGEBUILDER_COMPONENT') }}</span>
							<i class="fas fa-times-circle icon-remove" @click="restorePosition('component')"></i>
						</button>
					</li>
					<li class="nav-item" id="placeholder_message">
						<button type="button" class="nav-link drag" id="drag_message" draggable="true"
								@dragstart="drag($event)">
							<i class="fas fa-envelope"></i>
							<span>{{ translate('JLIB_PAGEBUILDER_MESSAGE') }}</span>
							<i class="fas fa-times-circle icon-remove" @click="restorePosition('message')"></i>
						</button>
					</li>
				</ul>
			</div>
			<div class="col col-12" style="height: 100px">
				<h2>{{ translate('JLIB_PAGEBUILDER_PREVIEW') }}</h2>
				<div v-html="renderPreview" :style="previewStyle"></div>
			</div>
		</div>

		<div class="pagebuilder" id="pagebuilder" :style="widthStyle">
			<h2>{{ translate('JLIB_PAGEBUILDER_VIEW') }}</h2>

			<!-- Element -->
			<draggable v-model="elementArray" handle=".handle">
				<item v-for="element in elementArray" :key="element.key" :class="['row-wrapper']"
					  :item="element" :handleRequired="true"
					  @delete="deleteElement({ element })"
					  @edit="editElement({ element })"></item>
			</draggable>
			<!-- Element Ends -->

			<button @click="addElement()" class="btn btn-success btn-block" type="button">
				<span class="icon-new"></span>
				<span>{{ translate('JLIB_PAGEBUILDER_ADD_ELEMENT') }}</span>
			</button>

			<!-- Modals -->
			<add-element-modal id="add-element"></add-element-modal>
		</div>
	</div>
</template>

<script>
    import {mapMutations, mapState} from 'vuex';
    import draggable from 'vuedraggable';
    import axios from "axios";

    export default {
        data() {
            return {
                renderPreview: ""
            }
        },
        computed: {
            ...mapState([
                'activeDevice',
                'resolution',
                'selectedSettings',
                'advancedSettings'
            ]),
            previewStyle() {
                this.loadAdvancedSettings()
                let height = 30;

                this.getChildrenSizeOfPageContent(this.elementArray)


                return {
                    'height': `${height}px`,
                    'background-color': `${this.advancedSettings.bgColor}`,
                    'color': `${this.advancedSettings.baseColor}`,
                }
            },
            widthStyle() {
                const deviceOrder = Object.keys(this.resolution);
                const activeIndex = deviceOrder.indexOf(this.activeDevice);
                const styles = {
                    'min-width': activeIndex === 0 ? 0 : this.resolution[this.activeDevice],
                    'max-width': activeIndex === deviceOrder.length - 1 ? '100%' : this.resolution[deviceOrder[activeIndex + 1]],
                };

                let styleString = '';
                for (const name in styles) {
                    styleString += `${name}: ${styles[name]}; `;
                }

                return styleString;
            },
            elementArray: {
                get() {
                    return this.$store.state.elementArray;
                },
                set(value) {
                    this.updateElementArray(value);
                }
            },
        },
        watch: {
            elementArray: {
                handler(newVal) {
                    document.getElementById('jform_params_grid').value = JSON.stringify(newVal);
                    this.liveViewPost(JSON.stringify(newVal));
                },
                deep: true,
            },
        },
        created() {
            this.mapElements(JSON.parse(document.getElementById('jform_params_grid').value));
            this.checkAllowedElements();
        },
        mounted() {
            this.loadAdvancedSettings();
            if (document.getElementsByClassName('drag_component').length) {
                let element = document.getElementsByClassName('drag_component')[0];
                element.appendChild(document.getElementById('drag_component'));
            }
            if (document.getElementsByClassName('drag_message').length) {
                let element = document.getElementsByClassName('drag_message')[0];
                element.appendChild(document.getElementById('drag_message'));
            }
            this.liveViewPost(JSON.stringify(this.elementArray));
        },
        methods: {
            ...mapMutations([
                'checkAllowedElements',
                'mapElements',
                'closeNav',
                'deleteElement',
                'setParent',
                'updateElementArray',
                'editElement',
                'updateGrid',
                'restorePosition',
                'setAdvancedSettings'
            ]),
            addElement() {
                this.setParent(this.elementArray);
                this.$modal.show('add-element');
            },
            drag(event) {
                event.dataTransfer.setData('text', event.target.id);
            },
            getChildrenSizeOfPageContent(rootPoint) {

            },
            loadAdvancedSettings() {
                this.setAdvancedSettings({
                    bgColor: document.getElementById('jform_params_baseBG').value,
                    baseColor: document.getElementById('jform_params_baseColor').value,
                    linkColor: document.getElementById('jform_params_linkColor').value
                })
            },
            liveViewPost(data) {
                let dataConf = {
                    task: 'ajax.fetchAssociations',
                    format: 'json',
                    data: window.btoa(data),
                    baseBG: `${this.advancedSettings.bgColor}`,
                    baseColor: `${this.advancedSettings.baseColor}`,
                    linkColor: `${this.advancedSettings.linkColor}`,
                    action: 'pagebuilder_liveview'
                };
                const queryString = Object.keys(dataConf).reduce((a, k) => {
                    a.push(`${k}=${encodeURIComponent(dataConf[k])}`);
                    return a;
                }, []).join('&');
                const url = `${document.location.href}&${queryString}`;
                axios.get(url).then((res) => {
                    this.renderPreview = res.data.data;
                })
            }
        },
        components: {
            draggable
        }
    };
</script>

<style lang="scss">
	#renderPreview {
		height: 100px;

		div {
			height: 50px;
			border: solid 1px black;

		}
	}

	.pagebuilder_container {
		width: 100%;
	}

	#fieldset-pagebuilder div {

	}

	.sidebar .closeButton {
		float: right;
	}

	.sidebar .sidebar-content {
		margin-left: 15px;
		margin-top: 12.5px
	}
</style>
