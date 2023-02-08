<script setup>
import VueBootstrapAutocomplete from './components/VueBootstrapAutocomplete.vue'
import '~bootstrap/scss/bootstrap.scss'

const query = ''

</script>

<template>

	<vue-bootstrap-autocomplete 
	v-model="query" 
	size='sm'
    :data="['Canada', 'Canada1', 'Canada2', 'Canada4', 'United States', 'Mexico', 'Netherlands']"
	no-results-info="No results found"
	/>
</template>