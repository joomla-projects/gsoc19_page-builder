<template>
	<ul id="devices" class="nav nav-pills">
		<li class="nav-item">
			<span class="nav-link">{{ translate('COM_TEMPLATES_SELECT_DEVICE')}}</span>
		</li>
		<li class="nav-item">
			<button @click="setDevice('xs', $event)" class="btn btn-info" type="button">
				<span class="fas fa-mobile-alt fa-lg" aria-hidden="true"></span>
				{{ translate('COM_TEMPLATES_DEVICE_XS') }}
			</button>
		</li>
		<li class="nav-item">
			<button @click="setDevice('sm', $event)" class="btn btn-info active" type="button">
				<span class="fas fa-tablet-alt fa-lg" aria-hidden="true"></span>
				{{ translate('COM_TEMPLATES_DEVICE_SM') }}
			</button>
		</li>
		<li class="nav-item">
			<button @click="setDevice('md', $event)" class="btn btn-info" type="button">
				<span class="fas fa-tablet-alt fa-lg fa-rotate-270" aria-hidden="true"></span>
				{{ translate('COM_TEMPLATES_DEVICE_MD') }}
			</button>
		</li>
		<li class="nav-item">
			<button @click="setDevice('lg', $event)" class="btn btn-info" type="button">
				<span class="fas fa-laptop fa-lg" aria-hidden="true"></span>
				{{ translate('COM_TEMPLATES_DEVICE_LG') }}
			</button>
		</li>
	</ul>
</template>

<script>
  import {mapMutations, mapState} from 'vuex';

  export default {
    name: 'devices',
    computed: {
      ...mapState([
        'resolution'
      ]),
    },
    methods: {
      ...mapMutations([
        'updateDeviceWidth'
      ]),
      setDevice(size, event) {
        this.updateDeviceWidth(size);

        const buttons = document.querySelectorAll('#devices > li > button');
        buttons.forEach(btn => {
          btn.classList.remove('active');
        });
        event.target.classList.add('active');
      }
    }
  };
</script>
