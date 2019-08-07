<template>
	<div>
		<div id="sidebar" class="sidebar">
			<div class="sidebar-content">
				<h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
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
						<div class="nav-link drag" id="drag_component" draggable="true" @dragstart="drag($event)">
							<i class="fas fa-file-alt"></i>
							<span>{{ translate('COM_TEMPLATES_COMPONENT') }}</span>
              <i class="fas fa-times-circle" @click="restorePosition($event, 'component')"></i>
						</div>
					</li>
					<li class="nav-item" id="placeholder_message">
						<div class="nav-link drag" id="drag_message" draggable="true" @dragstart="drag($event)">
							<i class="fas fa-envelope"></i>
							<span>{{ translate('COM_TEMPLATES_MESSAGE') }}</span>
              <i class="fas fa-times-circle" @click="restorePosition($event, 'message')"></i>
						</div>
					</li>
				</ul>
			</div>
		</div>

		<div class="pagebuilder" id="pagebuilder" :style="{ width: deviceWidth }">
			<h2>{{ translate('COM_TEMPLATES_VIEW') }}</h2>

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
				<span>{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}</span>
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
      deviceWidth() {
        return this.resolution[this.activeDevice];
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
      this.ifChildAllowed();
    },
    mounted() {
      if(document.getElementsByClassName('drag_component').length) {
        var element = document.getElementsByClassName('drag_component')[0];
        element.appendChild(document.getElementById('drag_component'));
      }
      if(document.getElementsByClassName('drag_message').length) {
        var element = document.getElementsByClassName('drag_message')[0];
        element.appendChild(document.getElementById('drag_message'));
      }
    },
    methods: {
      ...mapMutations([
        'ifChildAllowed',
        'mapElements',
        'closeNav',
        'deleteElement',
        'setParent',
        'updateElementArray',
        'editElement',
        'updateGrid',
      ]),
      addElement() {
        this.setParent(this.elementArray);
        this.$modal.show('add-element');
      },
      drag(event) {
        event.dataTransfer.setData('text', event.target.id);
      },
      restorePosition(event, location) {
        var element = document.getElementsByClassName('drag_' + location)[0];
        if(location == 'component') {
          element.__vue__.$data.element.options.component = false;
        }
        else {
          element.__vue__.$data.element.options.message = false;
        }
        element.classList.remove('drag_' + location);
        document.getElementById('placeholder_' + location).appendChild(document.getElementById('drag_' + location));
        this.updateGrid();
      }
    },
    components: {
      draggable
    }
  };
</script>
