<template>
	<div>
		<div id="sidebar" class="sidebar">
			<h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
			<hr>
			<button type="button" class="btn btn-lg closebtn" @click="closeNav()">
				<span class="icon-cancel"></span>
			</button>
			<!-- TODO Add to store -->
			<!-- <component :is="selectedSettings" class="form-group" :grid='grid_selected' :column='column_selected' @reset="reset"></component> -->
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

				<!-- Container & Module Position -->
				<div v-if="element.type != 'Grid'" class="element">
					<span class="desc">{{ element.type }} <span v-if="element.options.class">.{{ element.options.class }}</span></span>

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

						<div v-if="child.type == 'Grid'">
							<grid-element :grid="child" :grid-size="gridSize"></grid-element>
						</div>

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
				<div v-if="element.type == 'Grid'">
					<grid-element :grid="element" :grid-size="gridSize"></grid-element>
				</div>
				<!-- Grid Ends-->

				<button type="button" class="btn btn-add btn-outline-info btn-block test"
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
    data() {
      return {
        gridSize: 12, // TODO: save and load into grid param
      };
    },
    computed: {
      ...mapState([
        'elementArray',
        'childAllowed',
        'parent',
        'allowedChildren'
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
        'addContainer',
        'fillAllowedChildren',
        'mapGrid',
        'closeNav'
      ]),
      addElement(parent) {
        this.fillAllowedChildren(parent.type);
        this.$store.commit('addElement', parent);
        this.$modal.show('add-element');
      },
      updateGridBackground() {
        const rows = document.querySelectorAll('.pagebuilder .row-wrapper');
        Array.prototype.forEach.call(rows, row => {
          const percentageWidth = (1 / this.gridSize) * 100;
          const pixelWidth = (row.getBoundingClientRect().width / 100) * percentageWidth;
          row.style.backgroundSize = `${pixelWidth}px 150px`;
        });
      },
    }
  };
</script>
