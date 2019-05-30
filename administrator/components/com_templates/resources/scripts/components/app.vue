<template>
<div class="container-fluid row">
  <div id="Settings" class="settings col-sm-2">
      <h2>Settings</h2>
      <hr>
      <form>
        <div v-if="edit_position">
            <fieldset>
                <legend>Edit module position - {{ selected_pos }}</legend>

                <div class="form-group">
                    <label for="module_chrome">Select module chrome</label>
                    <select id="module_chrome" name="module_chrome" v-model="module_chrome">
                      <option value="none">none</option>
                      <option value="rounded">rounded</option>
                      <option value="table">table</option>
                      <option value="horz">horz</option>
                      <option value="xhtml">xhtml</option>
                      <option value="html5">html5</option>
                      <option value="outline">outline</option>
                    </select>
                </div>

                <div class="form-group">
                  <label for="size_input">Select module size</label>
                  <input id="size_input" name="size_input" type="text" v-model="size_input" class="form-control">
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-success" @click="save">Save</button>
                    <button type="button" class="btn btn-secondary" @click="removeActive">Back</button>
                </div>
            </fieldset>
        </div>

        <div v-else-if="edit_column" class="btn-group">
            <button type="button" class="btn btn-success" @click="">Save</button>
            <button type="button" class="btn btn-secondary" @click="">Back</button>
        </div>

      <div v-else-if="edit_grid" class="form-group">
        <fieldset>
            <legend>Edit Grid</legend>
            <label for="column_size">Add new column</label>
            <input id="column_size" name="column_size" type="text" v-model="column_size">

            <div class="btn-group">
                <button type="button" class="btn btn-success" @click="editGrid('',true)">Save</button>
                <button type="button" class="btn btn-danger" @click="back">Back</button>
            </div>
        </fieldset>
      </div>

      <div v-else>
          <fieldset>
            <legend>Add new grid</legend>
            <label for="grid_type">Select grid system(should add to 12)</label>
            <input id="grid_type" name="grid_type" type="text" v-model="grid_system">

            <div class="btn-group">
                <button type="button" class="btn btn-primary" @click="addGrid">Add</button>
                <button type="button" class="btn btn-secondary" @click="grid_system = ''">Reset</button>
            </div>
          </fieldset>
      </div>
    </form>
  </div>

  <div id="View" class="col-sm-10">
    <h2>View</h2>
    <draggable v-model="gridArray">
      <div v-for="grid in gridArray" v-model="gridArray" class="draggable">
        <draggable v-model="grid.children" class="row grid-row">
          <div class="list-group-item" v-for="column in grid.children" :class="[column.options.size]" @click="">
            {{column.options.size}}
            (<i>{{column.type}}</i>)<button type="button" class="remove" @click="deleteColumn(grid,column)">X</button>
          </div>
        </draggable>
        <button type="button" class="btn-primary btn editGrid" @click="editGrid(grid,false)">Edit Grid</button>
        <button type="button" class="btn btn-danger" @click="deleteGrid(grid)">Delete Grid</button>
      </div>
    </draggable>
  </div>
</div>
</template>

<script>
  import draggable from 'vuedraggable'

  export default {
    data() {
      return {
        myArray: [],
        element: '',
        key: 3,
        module_chrome: 'none',
        size_input: '3',
        edit_grid: false,
        edit_column: false,
        edit_position: false,
        selected_pos: '',
        grid_system: '',
        grid_selected: '',
        column_size: '',
        gridArray:
        [
          {
            type: 'grid',
            options: {},
            children:
            [
              {
                type: 'column',
                options: {
                  size: 'col-sm-4'
                },
                children: {

                }
              },
              {
                type: 'column',
                options: {
                  size: 'col-sm-4'
                },
                children: {

                }
              },
              {
                type: 'column',
                options: {
                  size: 'col-sm-4'
                },
                children: {

                }
              }
            ]
          }
        ]
      }
    },
    components: {
      draggable
    },
    methods: {
      add() {
        this.key++;
        this.myArray.push({
          name: "pos-" + this.key ,
          module_chrome: this.module_chrome,
          size: "col-sm-" + this.size_input
        }
        );
        this.module_chrome = 'none';
        this.size_input = '3';
      },
      addGrid() {
        var gridSize = this.grid_system.split(" ");
        this.myArray = [];
        gridSize.forEach(element => {
          this.myArray.push({
            type: 'column',
            options: {
              size: "col-sm-" + element
            },
            children: {

            }
          })
        });
        this.gridArray.push({
          type: 'grid',
          options: {},
          children: this.myArray
        });
        this.grid_system = '';
      },
      remove() {
        this.myArray.pop();
        this.key--;
        this.module_chrome = 'none';
        this.size_input = '3';
      },
      deleteGrid(grid) {
        var index = this.gridArray.indexOf(grid);
        if(index > -1)
          this.gridArray.splice(index,1);
      },
      deleteColumn(grid,column) {
        var index = grid.children.indexOf(column);
        if(index > -1){
          grid.children.splice(index,1);
        }
      },
      editColumn(element,grid) {
        this.edit_column = true;

      },
      editGrid(grid,submit) {
        if(submit){
          this.grid_selected.children.push({
            type: 'column',
            options: {
              size: "col-sm-" + this.column_size
            },
            children: {

            }
          })
          this.grid_selected = '';
          this.column_size = '';
          this.edit_grid = false;
        }
        else{
          this.edit_grid = true;
          this.grid_selected = grid;
        }
      },
      back() {
        this.edit_grid = false;
        this.edit_column = false;
        this.edit_position = false;
      }
    }
  }
</script>
