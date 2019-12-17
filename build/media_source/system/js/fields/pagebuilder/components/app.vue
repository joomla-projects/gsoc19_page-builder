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
			<div class="col-9">
				<devices></devices>
			</div>
			<div class="col">
				<ul class="nav nav-pills">
					<li class="nav-item" id="placeholder_component">
						<button type="button" class="nav-link drag" id="drag_component" draggable="true" @dragstart="drag($event)">
							<i class="fas fa-file-alt"></i>
							<span>{{ translate('JLIB_PAGEBUILDER_COMPONENT') }}</span>
							<i class="fas fa-times-circle icon-remove" @click="restorePosition('component')"></i>
						</button>
					</li>&nbsp;&nbsp;
					<li class="nav-item" id="placeholder_message">
						<button type="button" class="nav-link drag" id="drag_message" draggable="true" @dragstart="drag($event)">
							<i class="fas fa-envelope"></i>
							<span>{{ translate('JLIB_PAGEBUILDER_MESSAGE') }}</span>
							<i class="fas fa-times-circle icon-remove" @click="restorePosition('message')"></i>
						</button>
					</li>
				</ul>
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
          'min-width':'300px;', //activeIndex === 0 ? 0 : this.resolution[this.activeDevice],
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
        },
        deep: true,
      },
    },
    created() {
      this.mapElements(JSON.parse(document.getElementById('jform_params_grid').value));
      this.checkAllowedElements();
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
    },
    components: {
      draggable
    }
  };
</script>
