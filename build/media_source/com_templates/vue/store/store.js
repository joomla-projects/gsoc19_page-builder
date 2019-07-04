import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

const state = {
    sizeArray: [],
    elementSelected: '',
    columnSelected: '',
    selectedSettings: '',
    parent: '',
    allowedChildren: [],
    childAllowed: [],
    elements: window.Joomla.getOptions('com_templates').elements,
    elementArray: {}
};

const mutations = {
    mapGrid(state, payload) {
        state.elementArray = payload;
    },
    ifChildAllowed(state) {
        state.elements.forEach(el => {
            if(el.children)
                state.childAllowed.push(el.name);
        })
    },
    addGrid(state, sizes) {
        state.sizeArray = [];
        sizes.forEach(size => {
            state.sizeArray.push({
                type: 'Column',
                options: {
                size: 'col-' + size,
                class: ''
                },
                children: []
            });
        });
        if(state.parent.children) {
            state.parent.children.push({
                type: 'Grid',
                options: {
                class: '',
                },
                children: state.sizeArray
            });
        }
        else {
            state.parent.push({
                type: 'Grid',
                options: {
                  class: '',
                },
                children: state.sizeArray
            });
        }
    },
    deleteElement(state, element) {
        const index = state.elementArray.indexOf(element);
        if (index > -1)
        state.elementArray.splice(index, 1);
    },
    editElement(state, element) {
        state.selectedSettings = 'edit-element';
        state.elementSelected = element;
        document.getElementById("sidebar").style.width = "250px";
        document.getElementById("pagebuilder").style.marginLeft = "250px";
    },
    closeNav(state) {
        document.getElementById("sidebar").style.width = "0";
        document.getElementById("pagebuilder").style.marginLeft = "0";
    },
    addContainer(state) {
        if(state.parent.children) {
        state.parent.children.push({
            type: 'Container',
            options: {
            class: ''
            },
            children: []
        })
        }
        else {
        state.parent.push({
            type: 'Container',
            options: {
            class: ''
            },
            children: []
        })
        }
    },
    fillAllowedChildren(state, name) {
        state.allowedChildren = [];

        // Check if parent is root
        if(name == undefined)
        name = 'root';
        state.elements.forEach(el => {
        el.parent.forEach(item => {
            if(item == name)
            state.allowedChildren.push({
                'name': el.name,
                'description': el.description
            });
        })
        })
    },
    addElement(state, parent) {
        state.parent = parent;
    },
    deleteColumn(state, payload) {
        const index = payload.element.children.indexOf(payload.column);
        if (index > -1) {
            payload.element.children.splice(index, 1);
        }
    },
    insertElem(state, element) {
        if(state.parent.children) {
            state.parent.children.push({
                type: element,
                options: {
                    class: ''
                },
                children: []
            })
        }
        else {
            state.parent.push({
                type: element,
                options: {
                    class: ''
                },
                children: []
            })
        }
    },
    modifyElement(state, payload) {
        state.elementSelected.options.class = payload;
    },
}

export default new Vuex.Store({
    state,
    mutations,
})