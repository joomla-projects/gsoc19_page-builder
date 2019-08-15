<template>
	<ul id="devices" class="nav nav-pills">
		<li class="nav-item">
			<button @click="setDevice('xs', $event)" id="xs" class="btn btn-info" type="button">
				<span class="fas fa-mobile-alt fa-lg" aria-hidden="true"></span>
				<span class="sr-only">{{ translate('JLIB_PAGEBUILDER_DEVICE_XS_SR_DESC') }}</span>
			</button>
		</li>
		<li class="nav-item">
			<button @click="setDevice('sm', $event)" id="sm" class="btn btn-info" type="button">
				<span class="fas fa-tablet-alt fa-lg" aria-hidden="true"></span>
				<span class="sr-only">{{ translate('JLIB_PAGEBUILDER_DEVICE_SM_SR_DESC') }}</span>
			</button>
		</li>
		<li class="nav-item">
			<button @click="setDevice('md', $event)" id="md" class="btn btn-info" type="button">
				<span class="fas fa-tablet-alt fa-lg fa-rotate-270" aria-hidden="true"></span>
				<span class="sr-only">{{ translate('JLIB_PAGEBUILDER_DEVICE_MD_SR_DESC') }}</span>
			</button>
		</li>
		<li class="nav-item">
			<button @click="setDevice('lg', $event)" id="lg" class="btn btn-info" type="button">
				<span class="fas fa-laptop fa-lg" aria-hidden="true"></span>
				<span class="sr-only">{{ translate('JLIB_PAGEBUILDER_DEVICE_LG_SR_DESC') }}</span>
			</button>
		</li>
		<li class="nav-item">
			<button @click="setDevice('xl', $event)" id="xl" class="btn btn-info" type="button">
				<span class="fas fa-desktop fa-lg" aria-hidden="true"></span>
				<span class="sr-only">{{ translate('JLIB_PAGEBUILDER_DEVICE_XL_SR_DESC') }}</span>
			</button>
		</li>
		<li class="nav-item">
			<span id="device-desc" class="nav-link"></span>
		</li>
	</ul>
</template>

<script>
  import {mapMutations, mapState} from 'vuex';

  export default {
    name: 'devices',
    computed: {
      ...mapState([
        'activeDevice',
      ]),
      deviceDesc() {
        return document.getElementById('device-desc');
      },
    },
    mounted() {
      document.querySelector(`#devices #${this.activeDevice}`).classList.add('active');
      this.deviceDesc.innerText = this.translate(`JLIB_PAGEBUILDER_DEVICE_${this.activeDevice.toUpperCase()}`);
    },
    methods: {
      ...mapMutations([
        'updateDeviceWidth',
      ]),
      setDevice(size, event) {
        this.updateDeviceWidth(size);

        const buttons = document.querySelectorAll('#devices button');
        buttons.forEach(btn => {
          btn.classList.remove('active');
        });

        this.deviceDesc.innerText = this.translate(`JLIB_PAGEBUILDER_DEVICE_${size.toUpperCase()}`);
      },
    },
  };
</script>
