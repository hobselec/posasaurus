<template>
  <div>
    <div class="pl-1 pb-2 pt-3">Selected Country: {{ query }}</div>
    <vue-bootstrap-autocomplete
      class="mb-4"
      :data="[
        'Canada',
        'United Kingdom',
        'United States',
        'Mexico',
        'Netherlands',
      ]"
      v-model="query"
      showOnFocus
      placeholder="Choose a country"
      @blur="() => onEvent('blur')"
      @focus="() => onEvent('focus')"
      @hit="() => onEvent('hit')"
      @input="() => onEvent('input')"
      @keyup="() => onEvent('keyup')"
      @paste="() => onEvent('paste')"
    />
  </div>
</template>

<script>
import "bootstrap-vue/dist/bootstrap-vue.css";
import VueBootstrapAutocomplete from "../../../src/components/VueBootstrapAutocomplete";

export default {
  name: "EventsDemo",
  components: { VueBootstrapAutocomplete },
  data() {
    return {
      query: "",
    };
  },
  methods: {
    onEvent(event) {
      this.$bvToast.toast(event, {
        title: "Event emitted",
        autoHideDelay: 5000,
        variant: "info",
        solid: true,
      });
    },
  },
};
</script>

<style lang="scss">
@import "bootstrap/scss/bootstrap.scss";
</style>
