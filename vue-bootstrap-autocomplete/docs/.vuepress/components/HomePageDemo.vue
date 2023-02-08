<template>
  <div>
    <div class="pl-1 pb-2 pt-3">Selected Country: {{query}}</div>
    <vue-bootstrap-autocomplete
      class="mb-4"
      :data="['Canada', 'United Kingdom', 'United States', 'Mexico', 'Netherlands']"
      v-model="query"
      showOnFocus
      placeholder="Choose a country"
    />
  </div>
</template>

<script>
  import 'bootstrap-vue/dist/bootstrap-vue.css'
  import VueBootstrapAutocomplete from "../../../src/components/VueBootstrapAutocomplete";

  export default {
    name: "HomePageDemo",
    components: {VueBootstrapAutocomplete},
    data() {
      return {
        query: ''
      }
    }
  }
</script>

<style lang="scss">
  @import 'bootstrap/scss/bootstrap.scss';
</style>
