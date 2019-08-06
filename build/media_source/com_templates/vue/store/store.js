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
  maxKey: 0,
  deviceWidth: '768px',
  activeDevice: 'sm',
  resolution: {
    lg: '1200px',
    md: '992px',
    sm: '768px',
    xs: '576px',
  }
};

const mutations = {
  mapElements(state, elements) {
    state.elementArray = elements;
    state.parent = elements;
    mutations.setMaxKey(state);
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
    state.parent = parent.children || parent;
  },
  addElement(state, {name, config, moduleposition_name}) {
    let newElement = {};
    state.maxKey += 1;

    if (name === 'grid' && config) {
      newElement = mutations.getGrid(state, config);
    }
    else if (name === 'column') {
      newElement = {
        key: state.maxKey,
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
    else if (name === 'moduleposition' && moduleposition_name) {
      newElement = {
        key: state.maxKey,
        type: 'moduleposition',
        title: 'Module Position',
        options: {
          class: '',
          name: moduleposition_name,
          module_chrome: 'none'
        },
        children: []
      };
    }
    else {
      const type = state.elements.find(el => el.id === name);
      newElement = {
        key: state.maxKey,
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
    const gridKey = state.maxKey;
    const children = [];

    sizes.forEach(size => {
      state.maxKey += 1;

      children.push({
        key: state.maxKey,
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
      key: gridKey,
      type: 'grid',
      title: gridType.title,
      options: {
        class: '',
      },
      children: children,
    };
  },
  setMaxKey(state) {
    // Inner function that goes recursive through all items
    const findMaxKey = (elements) => {
      elements.forEach(element => {
        state.maxKey = Math.max(element.key,  state.maxKey);
        findMaxKey(element.children);
      });
    };

    findMaxKey(state.elementArray);
  },
  editElement(state, {element, parent}) {
    state.selectedSettings = 'edit-element';
    state.elementSelected = element;
    state.parent = parent;
    mutations.openNav();
  },
  deleteElement(state, {element, parent}) {
    const elements = parent ? parent.children : state.elementArray;
    const index = elements.indexOf(element);
    if (index > -1) {
      elements.splice(index, 1);
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
    if(state.elementSelected.type === 'moduleposition'){
      state.elementSelected.options.module_chrome = payload.module_chrome;
      state.elementSelected.options.name = payload.moduleposition_name;
    }
    if(state.elementSelected.type === 'column'){
      state.elementSelected.options.offset = payload.offset;
      state.elementSelected.options.offsetClass = payload.offsetClass;
    }
  },
  updateElementArray(state, payload) {
    state.elementArray = payload;
  },
  updateDeviceWidth(state, device) {
    state.deviceWidth = state.resolution[device];
    state.activeDevice = device;
  },
  updateChildrenOrder(state, {parent, children}) {
    parent.children = children;
  },
  updateSize(state, {element, size}) {
    element.options.size = size;
  },
};

export default new Vuex.Store({
  state,
  mutations,
});
