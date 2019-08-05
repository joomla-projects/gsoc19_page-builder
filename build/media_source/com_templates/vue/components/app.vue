<template>
	<div>
		<div id="sidebar" class="sidebar">
				<div class="sidebar-content">
				<h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
				<button type="button" class="btn btn-lg closebtn" @click="closeNav()">
					<span class="icon-cancel"></span>
				</button>

				<component :is="selectedSettings"></component>
			</div>
		</div>

    <div class="topbar row">
      <div class="col">
      <ul class="nav nav-pills">
        <li class="nav-item" @click="deviceWidth = '420px'">
          <a class="nav-link" data-toggle="pill" role="tab"><i class="fas fa-mobile-alt fa-lg"></i></a>
        </li>
        <li class="nav-item" @click="deviceWidth = '820px'">
          <a class="nav-link" data-toggle="pill" role="tab"><i class="fas fa-tablet-alt fa-lg"></i></a>
        </li>
        <li class="nav-item" @click="deviceWidth = '1050px'">
          <a class="nav-link" data-toggle="pill" role="tab"><i class="fas fa-laptop fa-lg"></i></a>
        </li>
        <li class="nav-item" @click="deviceWidth = '1250px'">
          <a class="nav-link active" data-toggle="pill" role="tab"><i class="fas fa-desktop fa-lg"></i></a>
        </li>
      </ul>
      </div>
      <div class="col">
        <ul class="nav nav-pills">
          <li class="nav-item">
            <div class="nav-link" id="dragComponent" draggable="true" @dragstart="drag($event)">
              <i class="fas fa-file-alt"></i>
              {{ translate('COM_TEMPLATES_COMPONENT') }}
            </div>
          </li>
          <li>
            <div class="nav-link" id="dragMessage" draggable="true" @dragstart="drag($event)">
              <i class="fas fa-envelope"></i>
              {{ translate('COM_TEMPLATES_MESSAGE') }}
            </div>
          </li>
        </ul>
      </div>
    </div>

		<div class="pagebuilder" id="pagebuilder" :style="{ width: deviceWidth }">
			<h2>{{ translate('COM_TEMPLATES_VIEW') }}</h2>

			<!-- Element -->
      <draggable v-model="elementArray" handle=".handle">
        <item v-for="element in elementArray" :key="element.key" :class="['row-wrapper']"
            :item="element" :handleRequired="true" @delete="deleteElement({ element })" @edit="editElement({ element })"></item>
      </draggable>
			<!-- Element Ends -->

			<button @click="addElement()" class="btn btn-success btn-block" type="button">
				<span class="icon-new"></span>
				<span>{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}</span>
			</button>

			<!-- Modals -->
			<add-element-modal id="add-element"></add-element-modal>
		</div>
	</div>
</template>

<script>
  import {mapMutations, mapState} from 'vuex';
  import draggable from 'vuedraggable'

  export default {
    computed: {
      ...mapState([
        'selectedSettings'
      ]),
      elementArray: {
        get() {
          return this.$store.state.elementArray;
        },
        set(value) {
          this.updateElementArray(value);
        }
      },
      deviceWidth: {
        get() {
          return this.$store.state.deviceWidth;
        },
        set(value) {
          this.updateDeviceWidth(value);
        }
      }
    },
    watch: {
      elementArray: {
        handler(newVal) {
          document.getElementById('jform_params_grid').value = JSON.stringify(newVal);
        },
        deep: true,
      },
    },
    created() {
      this.mapElements(JSON.parse(document.getElementById('jform_params_grid').value));
      this.ifChildAllowed();
    },
    methods: {
      ...mapMutations([
        'ifChildAllowed',
        'mapElements',
        'closeNav',
        'deleteElement',
        'setParent',
        'updateElementArray',
        'updateDeviceWidth',
        'editElement'
      ]),
      addElement() {
        this.setParent(this.elementArray);
        this.$modal.show('add-element');
      },
      drag(event) {
        event.dataTransfer.setData("text", event.target.id);
	    }
    },
    components: {
      draggable
    }
  };
</script>
