<template>
	<div>
		<div id="sidebar" class="sidebar">
			<div class="sidebar-content">
				<h2>{{ translate('JLIB_PAGEBUILDER_SETTINGS') }}</h2>
				<button type="button" class="btn btn-lg closebtn" @click="closeNav()">
					<span class="icon-cancel"></span>
				</button>

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
		</div>
		<div class="row">
			<div class="col" style="height: 500px">
				<h2>{{ translate('JLIB_PAGEBUILDER_PREVIEW') }}</h2>
				<!--<div v-html="renderPreview" :style="previewStyle" id="renderPreview"></div>-->
				<iframe ref="previewFrame" :src="previewUrl" id="iframe"></iframe>
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

  export default {
    data() {
      return {
        renderPreview: '',
        previewUrl: '',
        baseUrl: ''
      }
    },
    computed: {
      ...mapState([
        'activeDevice',
        'resolution',
        'selectedSettings',
        'advancedSettings'
      ]),
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

      let getUrl = window.location;
      this.baseUrl = getUrl.protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1];
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
      updatePreviewUrls() {
        this.$refs.previewFrame.contentWindow.location.reload(true);
        this.previewUrl = this.baseUrl + `${Math.random()}`;

        let _self = this;
        // Trigger iFrame reload
        setTimeout(() => {
          _self.previewUrl = _self.baseUrl;
        }, 10);
      },
      liveViewPost(data) {
        let tempId = location.href.split(/&|\?/).filter(v => v.match(/^id/))[0].replace("id=", "");

        let dataConf = {
          task: 'ajax.fetchAssociations',
          format: 'json',
          data: window.btoa(data),
          template_id: tempId,
          frontend_url: window.btoa(this.previewUrl),
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

        let _self = this;

        Joomla.request(
            {
              url: url,
              method: 'GET',
              data: '',
              perform: true,
              headers: {'Content-Type': 'application/json;charset=utf-8'},
              onSuccess: function (response) {
                response = JSON.parse(response);
                _self.renderPreview = response.data;
                _self.updatePreviewUrls();
              },
              onError: function (xhr) {
                // Remove js messages, if they exist.
                Joomla.removeMessages();
                Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
              }
            }
        );
      }
    },
    components: {
      draggable
    }
  };
</script>

<style lang="scss">
	#renderPreview {
		height: 230px;

		div {
			height: 50px;

			.column {
				border: solid 1px #c1b9b9;
				background: #acacae;
			}
		}
	}

	iframe {
		width: 130%;
		height: 520px;
		border: 1px solid black;
		zoom: 0.15;
		-moz-transform: scale(0.75);
		-moz-transform-origin: 0 0;
		-o-transform: scale(0.75);
		-o-transform-origin: 0 0;
		-webkit-transform: scale(0.75);
		-webkit-transform-origin: 0 0;
	}
</style>
