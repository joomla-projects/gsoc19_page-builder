import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

const state = {
  elementSelected: '',
  columnSelected: '',
  selectedSettings: '',
  parent: '',
  allowedChildren: [],
  childAllowed: [],
  elements: window.Joomla.getOptions('com_templates').elements,
  elementArray: {},
  gridSize: 12, // TODO: save and load into grid param
};

const mutations = {
  mapElements(state, elements) {
    state.elementArray = elements;
    state.parent = elements;
  },
  ifChildAllowed(state) {
    state.elements.forEach(el => {
      if (el.children) {
        state.childAllowed.push(el.id);
      }
    });
  },
  fillAllowedChildren(state, name) {
    name = name || 'root';
    state.allowedChildren = [];

    state.elements.forEach(el => {
      el.parent.forEach(item => {
        if (item === name) {
          state.allowedChildren.push({
            'title': el.title,
            'id': el.id,
            'description': el.description
          });
        }
      });
    });
  },
  setParent(state, parent) {
    mutations.fillAllowedChildren(state, parent.type);
    state.parent = parent;
  },
  addElement(state, {name, config}) {
    let newElement = {};

    if (name === 'grid' && config) {
      newElement = mutations.getGrid(state, config);
    }
    else if (name === 'column') {
      newElement = {
        key: mutations.getNextKey(state, state.parent),
        type: 'column',
        title: 'Column',
        options: {
          size: 12,
          class: '',
          offset: {
            xs: '',
            sm: '',
            md: '',
            lg: ''
          },
          offsetClass: '',
        },
        children: []
      };
    }
    else {
      const type = state.elements.find(el => el.id === name);
      newElement = {
        key: mutations.getNextKey(state, state.parent),
        type: name,
        title: type ? type.title : name,
        options: {
          class: ''
        },
        children: []
      };
    }

    state.parent.push(newElement);
  },
  getGrid(state, sizes) {
    const columnType = state.elements.find(el => el.id === 'column');
    const gridType = state.elements.find(el => el.id === 'grid');
    const children = [];

    sizes.forEach(size => {
      children.push({
        key: mutations.getNextKey(state, children),
        type: 'column',
        title: columnType.title,
        options: {
          size: size,
          class: '',
          offset: {
            xs: '',
            sm: '',
            md: '',
            lg: ''
          },
          offsetClass: '',
        },
        children: []
      });
    });

    return {
      key: mutations.getNextKey(state, state.parent),
      type: 'grid',
      title: gridType.title,
      options: {
        class: '',
      },
      children: children,
    };
  },
  getNextKey(state, elements) {
    let newKey = 0;
    elements.forEach(element => {
      newKey = Math.max(element.key, newKey);
    });
    return newKey + 1;
  },
  editElement(state, element) {
    state.selectedSettings = 'edit-element';
    state.elementSelected = element;
    mutations.openNav();
  },
  deleteElement(state, {element, parent}) {
    const elements = parent ? parent.children : state.elementArray;
    const index = elements.indexOf(element);
    if (index > -1) {
      elements.splice(index, 1);
    }
  },
  deleteColumn(state, payload) {
    const index = payload.element.children.indexOf(payload.column);
    if (index > -1) {
      payload.element.children.splice(index, 1);
    }
  },
  closeNav() {
    document.getElementById('sidebar').style.width = '0';
    document.getElementById('pagebuilder').style.marginLeft = '0';
  },
  openNav() {
    document.getElementById('sidebar').style.width = '250px';
    document.getElementById('pagebuilder').style.marginLeft = '250px';
  },
  modifyElement(state, payload) {
    state.elementSelected.options.class = payload.class;
    state.elementSelected.options.offset = payload.offset;
    state.elementSelected.options.offsetClass = payload.offsetClass;
  },
  updateGridSize(state, value) {
    state.gridSize = value;
    mutations.updateGridBackground(state);
  },
  updateGridBackground(state) {
    const rows = document.querySelectorAll('.pagebuilder .row-wrapper .item-content');
    Array.prototype.forEach.call(rows, row => {
      const percentageWidth = (1 / state.gridSize) * 100;
      const pixelWidth = (row.getBoundingClientRect().width / 100) * percentageWidth;
      row.style.backgroundSize = `${pixelWidth}px 150px`;
    });
  },
};

export default new Vuex.Store({
  state,
  mutations,
});
