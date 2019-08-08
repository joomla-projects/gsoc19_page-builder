<template>
	<div :class="['item', 'pagebuilder_' + element.type, element.options.class, element.options.component ? 'drag_component' : '', element.options.message ? 'drag_message' : '' ]" :id="element.type + '-' + element.key"
			@dragover.prevent @drop.prevent="drop($event)">

		<div v-if="handleRequired" class="btn-wrapper btn-group left">
			<i class="btn btn-primary btn-sm fa fa-align-justify handle"></i>
		</div>

		<div class="btn-wrapper btn-group right">
			<button type="button" class="btn btn-success btn-sm" @click="add(element)"
					v-if="childAllowed.includes(element.type)">
				<span class="icon-plus"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}</span>
			</button>
			<button type="button" class="btn btn-primary btn-sm" @click="$emit('edit')">
				<span class="icon-options"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT') }}</span>
			</button>
			<button type="button" class="btn btn-danger btn-sm" @click="$emit('delete')">
				<span class="icon-cancel"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_ELEMENT') }}</span>
			</button>
		</div>

		<div class="item-content">
			<div class="desc">
				<span>{{ element.title }}</span>
				<span v-if="element.options.class">.{{ element.options.class }}</span>
				<div v-for="detail in showDetails">
					<span>{{ detail.label }} - {{ detail.value }}</span>
				</div>
			</div>

			<grid v-if="element.type === 'grid'" :grid="element"></grid>

			<div v-else>
				<item v-for="child in element.children" :key="child.key" :item="child"
						@delete="deleteElement({element: child, parent: item})"
						@edit="editElement({element: child, parent: item})">
				</item>
			</div>
		</div>
	</div>
</template>

<script>
  import {mapGetters, mapMutations, mapState} from 'vuex';
  import {notifications} from "./../../app/Notifications";

  export default {
    name: 'item',
    props: {
      item: {
        required: true,
        type: Object,
      },
      handleRequired: {
        type: Boolean
      }
    },
    computed: {
      ...mapState([
        'childAllowed',
      ]),
      ...mapGetters([
        'getType'
      ]),
      showDetails() {
        const type = this.getType(this.item);

        if (type && type.config) {
          const show = [];
          for (const key in type.config) {
            if (type.config.hasOwnProperty(key) && type.config[key].show) {
              show.push({ label: type.config[key].label, value: this.item.options[key] });
            }
          }

          return show;
        }

        return [];
      }
    },
    data() {
      return {
        element: this.item
      };
    },
    methods: {
      ...mapMutations([
        'addElement',
        'editElement',
        'deleteElement',
        'fillAllowedChildren',
        'setParent',
        'updateGrid'
      ]),
      add(parent) {
        this.setParent(parent);

        if (parent.type === 'grid') {
          this.addElement({
            name: 'column',
            config: ''
          });
        } else {
          this.$modal.show('add-element');
        }
      },
      drop(event) {
        var classArray = Object.values(event.target.classList);
        if(classArray.includes('pagebuilder_moduleposition') || classArray.includes('col-offset')) {
          notifications.error('COM_TEMPLATES_NOT_SUPPORTED');
          return;
        }
        if(classArray.includes('drag_component') || classArray.includes('drag_message')) {
          return;
        }
        const data = event.dataTransfer.getData('text');
        event.target.appendChild(document.getElementById(data));
        event.target.classList.add(data);
        if(data == 'drag_component')
          event.target.__vue__.$data.element.options.component = true;
        else
          event.target.__vue__.$data.element.options.message = true;
        this.updateGrid();
      },
    },
  };
</script>
