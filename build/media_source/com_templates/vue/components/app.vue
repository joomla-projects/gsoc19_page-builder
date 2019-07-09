<template>
	<div>
		<div id="sidebar" class="sidebar">
			<h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
			<hr>
			<button type="button" class="btn btn-lg closebtn" @click="closeNav()">
				<span class="icon-cancel"></span>
			</button>

			<component :is="selectedSettings" class="form-group"></component>
		</div>

		<div class="pagebuilder" id="pagebuilder">
			<h2>{{ translate('COM_TEMPLATES_VIEW') }}</h2>
			<form name="global-settings">
				<div class="form-group">
					<label for="grid-size">{{ translate('COM_TEMPLATES_GRID_SIZE') }}</label>
					<input v-model.number="gridSize" type="number" id="grid-size" name="grid-size" class="form-control">
				</div>
			</form>

			<!-- TODO: make the rows sortable again ('draggable' breaks resizable elements) -->
			<!-- Element -->
			<div v-for="element in elementArray" :key="element.id" :class="['row-wrapper', element.type]">
				
				<span class="desc">{{ element.type }} <span v-if="element.options.class">.{{ element.options.class }}</span></span>
				<!-- Container & Module Position -->
				<div v-if="element.type != 'Grid'" class="element">

					<div class="btn-wrapper">
						<button type="button" class="btn btn-lg" @click="editElement(element)">
							<span class="icon-options"></span>
							<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_COLUMN') }}</span>
						</button>
						<button type="button" class="btn btn-lg" @click="deleteElement(element)">
							<span class="icon-cancel"></span>
							<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_COLUMN') }}</span>
						</button>
					</div>

					<div v-for="child in element.children" :class="['element', child.type]">
						<span class="desc">{{ child.type }} <span v-if="child.options.class">.{{ child.options.class }}</span></span>

						<grid-element v-if="child.type == 'Grid'" :grid="child"></grid-element>

						<button class="btn btn-add btn-outline-info" type="button"
								v-if="childAllowed.includes(child.type)"
								@click="addElement(child)">
							<span class="icon-new"></span>
							{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
						</button>
					</div>
				</div>
				<!-- Container & Module Position Ends -->

				<!-- Grid -->
				<grid-element v-if="element.type == 'Grid'" :grid="element"></grid-element>
				<!-- Grid Ends-->

				<button type="button" class="btn btn-add btn-outline-info btn-block"
						v-if="childAllowed.includes(element.type)" @click="addElement(element)">
					<span class="icon-new"></span>
					{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
				</button>
			</div>
			<!-- Element Ends -->

			<button @click="addElement(elementArray)" class="btn btn-add btn-outline-info btn-block" type="button">
				<span class="icon-new"></span>
				{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
			</button>

			<!-- Modals -->
			<add-element-modal id="add-element"></add-element-modal>

		</div>
	</div>
</template>

<script>
  import draggable from 'vuedraggable';
  import {mapMutations, mapState} from 'vuex';

  export default {
    computed: {
      ...mapState([
        'elementArray',
        'childAllowed',
		'gridSize',
		'selectedSettings'
      ]),
    },
    watch: {
      elementArray: {
        handler: function (newVal) {
          document.getElementById('jform_params_grid').value = JSON.stringify(newVal);
        },
        deep: true,
      },
      gridSize: {
        handler: function () {
          this.updateGridBackground();
        }
      },
    },
    components: {
      draggable
    },
    created() {
      this.mapGrid(JSON.parse(document.getElementById('jform_params_grid').value));
      this.ifChildAllowed();
    },
    methods: {
      ...mapMutations([
        'ifChildAllowed',
        'deleteElement',
        'editElement',
        'fillAllowedChildren',
        'mapGrid',
		'closeNav',
		'updateGridBackground',
      ]),
      addElement(parent) {
        this.fillAllowedChildren(parent.type);
        this.$store.commit('addElement', parent);
        this.$modal.show('add-element');
      },
    }
  };
</script>
