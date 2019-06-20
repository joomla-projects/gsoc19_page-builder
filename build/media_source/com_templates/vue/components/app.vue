<template>
	<div>
		<v-navigation-drawer v-model="showSettings" app absolute id="Settings" class="settings">
			<h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
			<hr>
			<!-- Settings for editing positions -->
			<edit-position v-if="edit_position" class="form-group" :grid="grid_selected" :column="column_selected" @reset="reset"></edit-position>

			<!-- Settings for editing grids -->
			<edit-grid v-else-if="edit_grid" class="form-group" :grid="grid_selected" @reset="reset"></edit-grid>

			<!-- Settings for adding columns -->
			<add-column v-else-if="add_column" class="form-group" :grid="grid_selected" @reset="reset"></add-column>

			<!-- Settings for editing columns -->
			<edit-column v-else-if="edit_column" class="form-group" :column="column_selected" @reset="reset"></edit-column>

		</v-navigation-drawer>

		<v-content class="pagebuilder">
			<h2>{{ translate('COM_TEMPLATES_VIEW') }}</h2>
			<form name="global-settings">
				<div class="form-group">
					<label for="grid-size">{{ translate('COM_TEMPLATES_GRID_SIZE') }}</label>
					<input v-model.number="gridSize" type="number" id="grid-size" name="grid-size" class="form-control">
				</div>
			</form>

			<!-- Grid -->
			<div v-model="gridArray">
				<div v-for="grid in gridArray" class="row-wrapper">
					<div class="btn-wrapper">
						<button type="button" class="btn btn-lg" @click="editGrid(grid)">
							<span class="icon-options"></span>
							<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_GRID') }}</span>
						</button>
						<button type="button" class="btn btn-lg" @click="deleteGrid(grid)">
							<span class="icon-cancel"></span>
							<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_GRID') }}</span>
						</button>
					</div>
					<span v-if="grid.options.class">.{{grid.options.class}}</span>

					<child-element v-for="column in grid.children" :element="column" :step="gridStep"
								   @edit="editColumn(column)" @add="show('add-element')"
								   @remove="deleteColumn(grid,column)"
					>
					</child-element>

					<button class="btn btn-add btn-outline-info" type="button" @click="addDefaultColumn(grid)">
						<span class="icon-new"></span>
						{{ translate('COM_TEMPLATES_ADD_COLUMN') }}
					</button>
				</div>
			</div>
			<!-- Grid Ends -->

			<button type="button" class="btn btn-outline-info btn-block" @click="addDefaultGrid">
				<span class="icon-new"></span>
				{{ translate('COM_TEMPLATES_ADD_GRID') }}
			</button>

			<!-- Modals -->
			<add-grid-modal id="add-grid" @selection="addGrid"></add-grid-modal>
			<add-module-modal id="add-module"></add-module-modal>
		</v-content>
	</div>
</template>

<script>
  import draggable from 'vuedraggable';

  export default {
    props: {
      grid: {
        required: false,
        default: function () {
          return JSON.parse(document.getElementById('jform_params_grid').value);
        },
      },
    },
    data() {
      return {
        add_column: false,
        edit_grid: false,
        edit_position: false,
        edit_column: false,
        grid_selected: '',
        column_selected: '',
        gridArray: this.grid,
        showSettings: false,
        gridSize: 12, // TODO: save and load into grid param
      };
    },
    mounted() {
      this.updateGridBackground();
    },
    computed: {
      gridStep: {
        get: function () {
          const wrapper = document.querySelector('.row-wrapper');
          const maxWidth = wrapper ? wrapper.clientWidth : document.querySelector('.pagebuilder').clientWidth;
          const gridStepPercentage = (1 / this.gridSize) * 100;
          // Resize component needs px values for grid steps
          return Math.round(maxWidth * (gridStepPercentage / 100));
        },
        set: function (newStep) {
          return newStep;
        },
      }
    },
    watch: {
      grid: {
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
    methods: {
      addGrid(sizes) {
        const myArray = [];
        sizes.forEach(size => {
          myArray.push({
            type: 'column',
            options: {
              size: size
            },
            children: []
          });
        });
        this.gridArray.push({
          type: 'grid',
          options: {},
          children: myArray
        });
        this.reset();
        this.hide('add-grid');
      },
      addDefaultGrid() {
        this.gridArray.push({
          type: 'grid',
          options: {},
          children: [
            {
              type: 'column',
              options: {
                size: 1,
              },
              children: [],
            }
          ],
        });
        this.updateGridBackground();
      },
      deleteGrid(grid) {
        const index = this.gridArray.indexOf(grid);
        if (index > -1)
          this.gridArray.splice(index, 1);
      },
      addColumn(grid) {
        this.reset();
        this.showSettings = true;
        this.grid_selected = grid;
        this.add_column = true;
      },
      addDefaultColumn(grid) {
        let nextAvailablePosition = 0;
        grid.children.forEach(child => {
          nextAvailablePosition += child.options.size;
		});
        grid.children.push({
          type: 'column',
          options: {
            size: 1,
			position: nextAvailablePosition,
          },
          children: [],
        });
      },
      deleteColumn(grid, column) {
        const index = grid.children.indexOf(column);
        if (index > -1) {
          grid.children.splice(index, 1);
        }
      },
      editPosition(grid, column) {
        this.reset();
        this.showSettings = true;
        this.grid_selected = grid;
        this.column_selected = column;
        this.edit_position = true;
      },
      editColumn(column) {
        this.reset();
        this.showSettings = true;
        this.column_selected = column;
        this.edit_column = true;
      },
      editGrid(grid) {
        this.reset();
        this.showSettings = true;
        this.edit_grid = true;
        this.grid_selected = grid;
      },
      reset() {
        this.edit_grid = false;
        this.edit_position = false;
        this.showSettings = false;
        this.add_column = false;
        this.grid_selected = '';
        this.column_selected = '';
      },
      log(el) {
        console.log(el);
      },
      show(name) {
        this.$modal.show(name);
      },
      hide(name) {
        this.$modal.hide(name);
      },
      updateGridBackground() {
        const rows = document.querySelector('.pagebuilder').querySelectorAll('.row-wrapper');
        Array.prototype.forEach.call(rows, row => {
          row.style.backgroundSize = `${this.gridStep}px 150px`;
        });
      },
    }
  };
</script>
