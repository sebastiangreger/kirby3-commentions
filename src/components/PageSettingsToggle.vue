<template>
  <div data-width="1/3" class="k-column">
    <div class="k-toggle-field k-field">
      <div data-theme="field" data-type="toggle" class="k-input">
        <span class="k-input-element">
          <label class="k-toggle-input">
            <input
              ref="input"
              :checked="setting.value"
              :id="setting.id"
              class="k-toggle-input-native"
              type="checkbox"
              @change="change($event.target.checked)"
            >
            <span class="k-toggle-input-label" v-html="label" />
          </label>
        </span>
      </div>
    </div>
  </div>
</template>

<script>
export default {

  props: {
    setting: Object,
  },

  computed: {
    label() {
      return this.setting.value ? this.setting.text[0] : this.setting.text[1];
    },
  },

  methods: {
    change(checked) {
      this.setting.value = checked;
      this.$emit('change', this.setting.id, checked);
    }
  }

}
</script>

<style lang="scss">
</style>
