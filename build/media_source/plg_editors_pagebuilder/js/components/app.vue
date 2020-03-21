<template>
	<div>
		<div id="sidebar" class="sidebar">
			<div class="sidebar-content">
				<h2>{{ translate('PLG_PAGEBUILDER_SETTINGS') }}</h2>
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
						<button type="button" class="nav-link drag" id="drag_component" draggable="true" @dragstart="drag($event)">
							<i class="fas fa-file-alt"></i>
							<span>{{ translate('PLG_PAGEBUILDER_COMPONENT') }}</span>
							<i class="fas fa-times-circle icon-remove" @click="restorePosition('component')"></i>
						</button>
					</li>
					<li class="nav-item" id="placeholder_message">
						<button type="button" class="nav-link drag" id="drag_message" draggable="true" @dragstart="drag($event)">
							<i class="fas fa-envelope"></i>
							<span>{{ translate('PLG_PAGEBUILDER_MESSAGE') }}</span>
							<i class="fas fa-times-circle icon-remove" @click="restorePosition('message')"></i>
						</button>
					</li>
				</ul>
			</div>
		</div>

		<div class="pagebuilder" id="pagebuilder" :style="widthStyle">
			<h2>{{ translate('PLG_PAGEBUILDER_VIEW') }}</h2>

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
				<span>{{ translate('PLG_PAGEBUILDER_ADD_ELEMENT') }}</span>
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
        storeField: null,
        jOptions: Joomla.getOptions('editor_pagebuilder'),
      }
    },
    computed: {
      ...mapState([
        'activeDevice',
        'resolution',
        'selectedSettings',
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
    created() {
      this.storeField = document.getElementById(this.jOptions.id);
      let initValue = [];

      const jsonComment = this.storeField.value.match(new RegExp('^<!--(.*)-->', 'g'));
      if (jsonComment) {
        try {
          initValue = JSON.parse(jsonComment[1]);
        } catch (e) {
          console.error('Could not parse initial value ', jsonComment);
          initValue = [];
        }
      }

      this.mapElements(initValue);
      this.checkAllowedElements();

      /** Register the editor's instance to Joomla Object */
      Joomla.editors.instances[this.jOptions.id] = {
        id: this.jOptions.id,
        instance: this,
        onSave: () => this.renderElements(),
      };

      /** On save * */
      this.storeField.form.addEventListener('submit', () => Joomla.editors.instances[this.jOptions.id].onSave());
    },
    mounted() {
      if(document.getElementsByClassName('drag_component').length) {
        let element = document.getElementsByClassName('drag_component')[0];
        element.appendChild(document.getElementById('drag_component'));
      }
      if(document.getElementsByClassName('drag_message').length) {
        let element = document.getElementsByClassName('drag_message')[0];
        element.appendChild(document.getElementById('drag_message'));
      }
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
        'restorePosition'
      ]),
      addElement() {
        this.setParent(this.elementArray);
        this.$modal.show('add-element');
      },
      drag(event) {
        event.dataTransfer.setData('text', event.target.id);
      },
      renderElements() {
        let result = '<!--' + JSON.stringify(this.elementArray) + '-->';
        const data = this.elementArray;

        Joomla.request({
          url: this.jOptions.renderUrl,
          headers: {
            'Content-Type': 'application/json',
          },
          method: 'POST',
          perform: true,
          data: data,
          onSuccess: (response) => {
            if (response) {
              result = response;
            }
          },
          onError: () => {
            console.error('Error on rendering!');
          }
        });

        return result;
      },
    },
    components: {
      draggable
    }
  };
</script>
