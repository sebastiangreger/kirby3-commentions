<template>
  <k-button
    v-bind="flag"
    class="k-commentions-item-status-flag"
  />
</template>

<script>
export default {
  props: {
    status: {
      type: String,
      default: 'pending',
    },
  },
  computed: {
    flag() {
      const flag = {
        icon: 'circle-outline',
        color: 'var(--color-notice)',
        theme: `commentions-${this.status}`,
        disabled: true,
      };

      if (this.status === 'approved') {
        flag.icon = 'circle';
      } else if (this.status === 'unapproved') {
        flag.icon = 'cancel';
      } else if (this.status === 'update') {
        flag.icon = 'circle-filled';
      }

      return flag;
    },
  },
};
</script>

<style lang="scss">
.k-commentions-item-status-flag[data-disabled] {
  opacity: 1;
}

.k-commentions-item-status-flag svg {
  height: 14px;
  width: 14px;
}

.k-commentions-item-status-flag[data-theme="commentions-pending"],
.k-commentions-item-status-flag[data-theme="commentions-update"] {
  color: var(--color-notice);
}

.k-commentions-item-status-flag[data-theme="commentions-approved"] {
  color: var(--color-positive-light);
}

.k-commentions-item-status-flag[data-theme="commentions-unapproved"] {
  color: var(--color-negative-light);
}
</style>
