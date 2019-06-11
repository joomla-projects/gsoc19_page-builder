<template>
<div class="container-fluid row" id="pageBuilder">
  <div v-if="showSettings" id="Settings" class="settings col-sm-2">
    <h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
    <hr>
    <form>
        <!-- Settings for editing positions -->
        <edit-position v-if="edit_position" class="form-group" v-bind:grid="grid_selected" v-bind:column="column_selected" v-on:reset="reset"></edit-position>

        <!-- Settings for editing grids -->
        <edit-grid v-if="edit_grid" class="form-group" v-bind:grid="grid_selected" v-on:reset="reset"></edit-grid>

        <!-- Settings for adding columns -->
        <add-column v-if="add_column" class="form-group" v-bind:grid="grid_selected" v-on:reset="reset"></add-column>

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
            <button type="button" class="icon-options close" @click="editColumn(column)"></button>
            <br>
            <!-- Module Position -->
            <div class="position container-fluid">
            <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#newmodule">+</button>
            <button type="button" class="icon-apply close" @click="editPosition(grid,column)"></button>
            </div>
          </div>
          <button class="list-group-item" type="button" @click="addColumn(grid)">+</button>
        </draggable>
        <!-- Column Ends-->

      </div>
    </draggable>
    <!-- Grid Ends -->

    <button type="button" class="btn btn-outline-info btn-block" @click="show('add-grid')">+</button>

    <!-- Modals -->
    <add-grid-modal id="add-grid" v-on:selection="addGrid"></add-grid-modal>
    <edit-column-modal id="edit-column" v-bind:column="currentColumn"></edit-column-modal>

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
  {{gridArray}}
</div>
</template>

<script>
  import draggable from 'vuedraggable';
  import {notifications} from "./../app/Notifications";
  import AddGridModal from './modals/modal-add-grid.vue';
  import EditColumnModal from './modals/modal-edit-column.vue';

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
        currentColumn: {options:{class:''}},
        myArray: [],
        module_chrome: 'none',
        size_input: '3',
        add_column: false,
        edit_grid: false,
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
    components: {
      AddGridModal,
      EditColumnModal,
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
      addColumn(grid) {
        this.reset();
        this.showSettings = true;
        this.grid_selected = grid;
        this.add_column = true;
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
        this.currentColumn = column;
        this.show('edit-column');
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
