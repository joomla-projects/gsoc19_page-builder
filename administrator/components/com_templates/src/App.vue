<template>
  <div class="container">
    <div class="row">Page Builder</div>
    <hr>

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
      
      <label for="size_input">Select module size</label>
      <input id="size_input" name="size_input" type="text" v-model="size_input">
    </div>

    <div class="form-group">
      <button class="btn btn-primary" @click="add">Add</button>
      <button class="btn btn-primary" @click="remove">Remove</button>
    </div>

    <draggable v-model="myArray">
      <div class='list-group-item' v-for="element in myArray" :key="key" :class="element.size">{{element.name}}</div>
    </draggable>

    <rawDisplayer class="col-3" :value="myArray" title="List" />

  </div>
</template>

<script>
  import draggable from 'vuedraggable'
  import rawDisplayer from './components/raw-displayer'

  export default {
    data() {
      return {
        myArray: [
        {
          name: 'pos-1',
          module_chrome: 'none',
          size: 'col-sm-3'
        },
        {
          name: 'pos-2',
          module_chrome: 'none',
          size: 'col-sm-3'
        },
        {
          name: 'pos-3',
          module_chrome: 'none',
          size: 'col-sm-3'
        }],
        element: '',
        key: 3,
        module_chrome: 'none',
        size_input: '3'
      }
    },
    components: {
      draggable,
      rawDisplayer
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
      remove() {
        this.myArray.pop();
        this.key--;
        this.module_chrome = 'none';
        this.size_input = '3';
      }
    },
  }
</script>

<style>
  button {
    margin: 0.1em
  }
  select,input {
    margin: 0.5em
  }
</style>
