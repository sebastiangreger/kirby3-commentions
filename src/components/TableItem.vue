<template>
  <tr>
    <td>{{ item.status }}</td>
    <td>{{ item.name }}</td>
    <td>
      <commention-text
        v-bind:text="item.text_sanitized"
        v-bind:source="item.text"
        v-bind:view-source="viewSource"
      />
    </td>
    <td class="k-commentions-table-item-options">
      <nav class="k-list-item-options">
        <slot name="options">
          <k-button
            tooltip="Actions"
            icon="dots"
            alt="Actions"
            class="k-list-item-toggle"
            @click.stop="$refs.options.toggle()"
          />
          <k-dropdown-content
            ref="options"
            :options="[
            {icon: 'edit', text: 'Edit'},
            {icon: 'trash', text: 'Delete'}
            ]"
            align="right"
            @action="$emit('action', $event)"
          />
        </slot>
      </nav>
    </td>
  </tr>
</template>

<script>

  import CommentionText from "./CommentionText.vue";

  export default {
    components: {
      'commention-text': CommentionText,
    },

    props: {
      status: String,
      viewSource: {
        type: Boolean,
        default: false,
      },
      item() {
        return {
          type: Object,
          default: {},
        };
      },
    }
  }

</script>

<style lang="scss">

  .k-commentions-table .k-commentions-table-item-options {
    overflow: visible;
  }

</style>
