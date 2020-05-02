<template>
  <k-dialog
    ref="dialog"
    :button="$t('delete')"
    theme="negative"
    icon="trash"
    @submit="submit"
  >
    <k-text v-html="message" />
  </k-dialog>
</template>

<script>
export default {
  data() {
    return {
      item: null
    };
  },

  computed: {
    message: function () {
      if (!this.item) {
        return '';
      }

      let translation = '';

      // Go to all types manually. Yep, it’s painfull but makes it
      // way easier to find out where translations are acutally used
      // via file search
      switch (this.item.type) {
        case 'webmention':
          translation = this.$t('commentions.section.delete.webmention.confirm');
          break;
        case 'mention':
          translation = this.$t('commentions.section.delete.mention.confirm');
          break;
        case 'trackback':
          translation = this.$t('commentions.section.delete.trackback.confirm');
          break;
        case 'pingback':
          translation = this.$t('commentions.section.delete.pingback.confirm');
          break;
        case 'like':
          translation = this.$t('commentions.section.delete.like.confirm');
          break;
        case 'bookmark':
          translation = this.$t('commentions.section.delete.bookmark.confirm');
          break;
        case 'reply':
          translation = this.$t('commentions.section.delete.reply.confirm');
          break;
        case 'comment':
          translation = this.$t('commentions.section.delete.comment.confirm');
          break;
        default:
          translation = this.$t('commentions.section.delete.unknown.confirm');
      }

      return translation;
    },
  },

  methods: {
    open(item) {
      this.item = item;
      this.$refs.dialog.open();
      this.$emit("open", this.item);
    },

    close() {
      this.$refs.dialog.close();
      this.$emit("close", this.item);
      this.item = null;
    },

    submit() {
      this.$refs.dialog.close();
      this.$emit("submit", this.item);
      this.item = null;
    }
  }
};
</script>
