<template>
<div class="container-fluid row" id="pageBuilder">
  <div v-if="showSettings" id="Settings" class="settings col-sm-2">
      <h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
      <hr>
      <form>
        <!-- Settings for editing positions -->
        <div v-if="edit_position">
            <fieldset>
                <legend>{{ translate('COM_TEMPLATES_EDIT_POSITION') }}</legend>
                <div class="form-group">
                    <label for="module_chrome">{{ translate('COM_TEMPLATES_SELECT_MODULE_CHROME') }}</label>
                    <select id="module_chrome" name="module_chrome" v-model="module_chrome">
                      <option value="none">{{ translate('COM_TEMPLATES_NONE') }}</option>
                      <option value="rounded">{{ translate('COM_TEMPLATES_ROUNDED') }}</option>
                      <option value="table">{{ translate('COM_TEMPLATES_TABLE') }}</option>
                      <option value="horz">{{ translate('COM_TEMPLATES_HORZ') }}</option>
                      <option value="xhtml">{{ translate('COM_TEMPLATES_XHTML') }}</option>
                      <option value="html5">{{ translate('COM_TEMPLATES_HTML5') }}</option>
                      <option value="outline">{{ translate('COM_TEMPLATES_OUTLINE') }}</option>
                    </select>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-success" @click="editPosition('','',true)">{{ translate('COM_TEMPLATES_SAVE') }}</button>
                    <button type="button" class="btn btn-secondary" @click="reset">{{ translate('JTOOLBAR_BACK') }}</button>
                </div>
            </fieldset>
        </div>

        <!-- Settings for editing columns -->
        <div v-else-if="edit_column" class="btn-group">
          <fieldset>
            <legend>{{ translate('COM_TEMPLATES_EDIT_COLUMN') }}</legend>
            <label for="column_class">{{ translate('COM_TEMPLATES_ADD_CLASS') }}</label>
            <input id="column_class" name="column_class" type="text" v-model="column_class">

            <button type="button" class="btn btn-success" @click="editColumn('','',true)">{{ translate('COM_TEMPLATES_SAVE') }}</button>
            <button type="button" class="btn btn-danger" @click="reset">{{ translate('JTOOLBAR_BACK') }}</button>
          </fieldset>
        </div>

      <!-- Settings for editing grids -->
      <div v-else-if="edit_grid" class="form-group">
        <fieldset>
            <legend>{{ translate('COM_TEMPLATES_EDIT_GRID') }}</legend>
            <label for="column_size">{{ translate('COM_TEMPLATES_ADD_COLUMN') }}</label>
            <input id="column_size" name="column_size" type="text" v-model="column_size">

            <label for="grid_class">{{ translate('COM_TEMPLATES_ADD_CLASS') }}</label>
            <input id="grid_class" name="grid_class" type="text" v-model="grid_class">

            <div class="btn-group">
                <button type="button" class="btn btn-success" @click="editGrid('',true)">{{ translate('COM_TEMPLATES_SAVE') }}</button>
                <button type="button" class="btn btn-danger" @click="reset">{{ translate('JTOOLBAR_BACK') }}</button>
            </div>
        </fieldset>
      </div>

      <!-- Settings for adding columns -->
      <div v-else-if="add_column" class="form-group">
          <fieldset>
                <legend>{{ translate('COM_TEMPLATES_ADD_COLUMN') }}</legend>
                <label for="column_size">{{ translate('COM_TEMPLATES_ADD_COLUMN') }}</label>
                <input id="column_size" name="column_size" type="text" v-model="column_size">

                <div class="btn-group">
                    <button type="button" class="btn btn-success" @click="addColumn('',true)">{{ translate('COM_TEMPLATES_SAVE') }}</button>
                    <button type="button" class="btn btn-danger" @click="reset">{{ translate('JTOOLBAR_BACK') }}</button>
                </div>
          </fieldset>
      </div>

    </form>
  </div>

  <div id="View" class="container-fluid">
    <h2>{{ translate('COM_TEMPLATES_VIEW') }}</h2>
    <!-- Grid -->
    <draggable v-model="gridArray">
      <div v-for="grid in gridArray" v-model="gridArray" class="draggable">
        <button v-if="gridArray.length" type="button" class="icon-cancel close" @click="deleteGrid(grid)"></button>
        <button v-if="gridArray.length" type="button" class="icon-apply close" @click="editGrid(grid,false)"></button>

        <!-- Column -->
        <draggable v-model="grid.children" class="row grid-row">
          <div class="list-group-item" v-for="column in grid.children" :class="[column.options.size]" @click="">
            {{column.options.size}}
            (<i>{{column.type}}</i>)
            <button type="button" class="icon-cancel close" @click="deleteColumn(grid,column)"></button>
            <button type="button" class="icon-apply close" @click="editColumn(grid,column,false)"></button>
            <br>
            <!-- Module Position -->
            <div class="position container-fluid">
            <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#newmodule">+</button>
            <button type="button" class="icon-apply close" @click="editPosition(grid,column,false)"></button>
            </div>
          </div>
          <button class="list-group-item" type="button" @click="addColumn(grid,false)">+</button>
        </draggable>
        <!-- Column Ends-->

      </div>
    </draggable>
    <!-- Grid Ends -->

    <button type="button" class="btn btn-outline-info btn-block" @click="show('add-grid')">+</button>

    <!-- Modals -->
    <add-grid-modal id="add-grid" v-on:selection="addGrid"></add-grid-modal>

    <!-- Modal for adding modules -->
    <div class="modal fade" id="newmodule" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5>{{ translate('COM_TEMPLATES_SELECT_MODULE') }}</h5>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal ends -->
  </div>
</div>
</template>

<script>
  import draggable from 'vuedraggable';
  import {notifications} from "./../app/Notifications";
  import AddGridModal from './modals/modal-add-grid.vue';

  export default {
    props: {
      grid: {
        type: Array,
        required: false,
        default: function () {
          return [];
        },
      },
    },
    data() {
      return {
        myArray: [],
        module_chrome: 'none',
        size_input: '3',
        add_column: false,
        edit_grid: false,
        edit_column: false,
        edit_position: false,
        grid_system: '',
        grid_selected: '',
        column_selected: '',
        column_size: '',
        grid_class: '',
        column_class: '',
        gridArray: this.grid,
        showSettings: false
      };
    },
    watch: {
      grid: {
        handler: function (newVal) {
          document.getElementById('jform_params_grid').value = JSON.stringify(newVal);
        },
        deep: true,
      },
    },
    computed: {
      gridValidate() {
        let sum = 0;
        const gridSize = this.grid_system.split(' ');
        gridSize.forEach(element => {
          sum = sum + Number(element);
        });
        return (sum === 12);
      }
    },
    components: {
      AddGridModal,
      draggable
    },
    methods: {
      addGrid(sizes) {
        this.myArray = [];
        sizes.forEach(size => {
          this.myArray.push({
            type: 'column',
            options: {
              size: 'col-' + size
            },
            children: [{
              type: 'position',
              options: {
                module_chrome: 'none'
              },
              children: []
            }]
          });
        });
        this.gridArray.push({
          type: 'grid',
          options: {},
          children: this.myArray
        });
        this.reset();
        this.hide('add-grid');
      },
      deleteGrid(grid) {
        const index = this.gridArray.indexOf(grid);
        if (index > -1)
          this.gridArray.splice(index, 1);
      },
      addColumn(grid, submit) {
        if (submit) {
          if (this.column_size !== '') {
            let sum = 0;
            this.grid_selected.children.forEach(element => {
              sum += Number(element.options.size.split('-')[2]);
            });
            if (sum + Number(this.column_size) <= 12) {
              this.grid_selected.children.push({
                type: 'column',
                options: {
                  size: 'col-sm-' + this.column_size
                },
                children: [{
                  type: 'position',
                  options: {},
                  children: []
                }]
              });
            }
            else {
                notifications.error('COM_TEMPLATES_MAX_COLUMN_SIZE');
            }
          }
          this.reset();
        }
        else {
          this.showSettings = true;
          this.add_column = true;
          this.grid_selected = grid;
        }
      },
      deleteColumn(grid, column) {
        const index = grid.children.indexOf(column);
        if (index > -1) {
          grid.children.splice(index, 1);
        }
      },
      editPosition(grid, column, submit) {
        if (submit) {
          if (this.module_chrome !== 'none') {
            this.column_selected.children[0].options.module_chrome = this.module_chrome;
          }
          this.module_chrome = 'none';
          this.reset();
        }
        else {
          this.showSettings = true;
          this.edit_position = true;
          this.column_selected = column;
          this.grid_selected = grid;
        }
      },
      editColumn(grid, column, submit) {
        if (submit) {
          if (this.column_class !== '') {
            const index = this.grid_selected.children.indexOf(this.column_selected);
            if (index > -1) {
              this.grid_selected.children[index].options.class = this.column_class;
            }
          }
          this.reset();
        }
        else {
          this.edit_column = true;
          this.showSettings = true;
          this.column_selected = column;
          this.grid_selected = grid;
        }
      },
      editGrid(grid, submit) {
        if (submit) {
          if (this.grid_class !== '') {
            this.grid_selected.options.class = this.grid_class;
          }

          if (this.column_size !== '') {
            let sum = 0;
            this.grid_selected.children.forEach(element => {
              sum += Number(element.options.size.split('-')[2]);
            });
            if (sum + Number(this.column_size) <= 12) {
              this.grid_selected.children.push({
                type: 'column',
                options: {
                  size: 'col-sm-' + this.column_size
                },
                children: [{
                  type: 'position',
                  options: {},
                  children: []
                }]
              });
            }
            else {
                notifications.error('COM_TEMPLATES_MAX_COLUMN_SIZE');
            }
          }
          this.reset();
        }
        else {
          this.edit_grid = true;
          this.grid_selected = grid;
          this.showSettings = true;
        }
      },
      reset() {
        this.edit_grid = false;
        this.edit_column = false;
        this.edit_position = false;
        this.showSettings = false;
        this.add_column = false;
        this.grid_selected = '';
        this.column_selected = '';
        this.column_size = '';
        this.column_class = '';
        this.grid_class = '';
        this.grid_system = '';
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
    }
  };
</script>
