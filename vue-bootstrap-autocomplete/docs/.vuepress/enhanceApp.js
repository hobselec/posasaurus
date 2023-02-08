import { ToastPlugin } from "bootstrap-vue";

export default ({ Vue, options, router, siteData, isServer }) => {
  Vue.use(ToastPlugin);
};
