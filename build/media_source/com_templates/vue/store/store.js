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
  componentAllowed: [],
  messageAllowed: [],
  elements: window.Joomla.getOptions('com_templates').elements,
  elementArray: {},
  maxKey: 0,
  size: 12,
  activeDevice: 'sm',
  resolution: {
    xl: '1200px',
    lg: '992px',
    md: '768px',
    sm: '576px',
    xs: '450px', // Smaller as 'sm'
  }
};

const mutations = {
  mapElements(state, elements) {
    state.elementArray = elements;
    state.parent = elements;
    mutations.setMaxKey(state);
  },
  checkAllowedElements(state) {
    state.elements.forEach(el => {
      if (el.children) {
        state.childAllowed.push(el.id);
      }
      if (el.component) {
        state.componentAllowed.push(el.id);
      }
      if (el.message) {
        state.messageAllowed.push(el.id);
      }
    });
  },
  fillAllowedChildren(state, name) {
    name = name || 'root';
    state.allowedChildren = [];

    state.elements.forEach(el => {
      el.parent.forEach(item => {
        if (item === name) {
          state.allowedChildren.push(el);
        }
      });
    });
  },
  setParent(state, parent) {
    mutations.fillAllowedChildren(state, parent.type);
    state.parent = parent.children || parent;
  },
  addElement(state, {name, config, childConfig}) {
    const type = state.elements.find(el => el.id === name);
    state.maxKey += 1;

    let newElement = {
      key: state.maxKey,
      type: type.id,
      title: type.title,
      options: {
        size: {
          xs: state.size,
          sm: 0,
          md: 0,
          lg: 0,
          xl: 0,
        },
        class: '',
        offset: {
          xs: 0,
          sm: 0,
          md: 0,
          lg: 0,
          xl: 0,
        },
      },
      children: []
    };

    // Merge config with element.config
    for (let key in config) {
      if (config.hasOwnProperty(key)) {
        newElement.options[key] = config[key];
      }
    }

    if (name === 'grid' && childConfig) {
      newElement.children = mutations.getConfiguredChildren(state, 'column', childConfig)
    }

    state.parent.push(newElement);
  },
  getConfiguredChildren(state, name, configs) {
    const type = state.elements.find(el => el.id === name);
    const children = [];

    // TODO: config could be anything (not only size) => make key-value object (?)
    if (configs) {
      configs.forEach(config => {
        state.maxKey += 1;

        children.push({
          key: state.maxKey,
          type: type.id,
          title: type.title,
          options: {
            size: {
              xs: config,
              sm: 0,
              md: 0,
              lg: 0,
              xl: 0,
            },
            class: '',
            offset: {
              xs: 0,
              sm: 0,
              md: 0,
              lg: 0,
              xl: 0,
            },
            offsetClass: '',
          },
          children: []
        });
      });
    }

    return children;
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
    if(state.elementSelected.type === 'column'){
      state.elementSelected.options.offset = payload.offset;
      state.elementSelected.options.offsetClass = payload.offsetClass;
    }
  },
  updateElementArray(state, payload) {
    state.elementArray = payload;
  },
  updateDeviceWidth(state, device) {
    state.activeDevice = device;
  },
  updateChildrenOrder(state, {parent, children}) {
    parent.children = children;
  },
  updateSize(state, {element, size}) {
    element.options.size[state.activeDevice] = size;
  },
  updateGrid(state) {
    document.getElementById('jform_params_grid').value = JSON.stringify(state.elementArray);
  },
};

const getters = {
  getElementSize: (state) => (element) => {
    if (element.options && element.options.size) {
      let size = element.options.size[state.activeDevice];

      // Get size above (mobile-first principle)
      if (!size) {
        const deviceOrder = ['xs', 's', 'md', 'lg', 'xl'];

        for (const device of deviceOrder) {
          if (element.options.size[device]) {
            return element.options.size[device];
          }
        }
      }

      return size;
    }

    return state.size;
  },
  getType: (state) => (element) => {
    return state.elements.find(el => el.id === element.type || element.id);
  },
};

export default new Vuex.Store({
  state,
  mutations,
  getters,
});
