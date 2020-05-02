<template>
  <li class="k-commentions-item">
    <div class="k-commentions-item-content">
      <div class="k-commentions-item-icon">
        <k-icon :type="icon"/>
      </div>
      <div class="k-commentions-item-text">
        <div class="k-commentions-item-header">
          <p
            class="k-commentions-item-source"
            v-html="item.source_formatted"
          />
          <time
            class="k-commentions-item-date"
            :datetime="item.timestamp"
          >
            {{ dateFormatted }}
          </time>
          <p v-if="item.email" class="k-commentions-item-email">
            <a :href="`mailto:${item.email}`">{{ item.email }}</a>
          </p>
        </div>
        <k-commentions-text
          v-if="item.text"
          v-bind:text="item.text_sanitized"
          v-bind:source="item.text"
          v-bind:view-source="viewSource"
        />
      </div>
    </div>
    <nav class="k-commentions-item-options">
      <k-commentions-status :status="item.status"/>
      <k-button
        v-if="true"
        :tooltip="$t('options')"
        icon="dots"
        :alt="Options"
        class="k-commentions-item-toggle"
        @click.stop="$refs.options.toggle()"
      />
      <k-dropdown-content
        ref="options"
        :options="options"
        align="right"
        @action="$emit('action', $event, item.uid, item.pageid)"
      />
    </nav>
  </li>
</template>

<script>
import Status from "./Status.vue";
import Text from "./Text.vue";

export default {
  components: {
    'k-commentions-status': Status,
    'k-commentions-text': Text,
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
  },

  data() {
    return {};
  },

  computed: {
    hasText() {
      return ['reply', 'comment'].includes(this.item.type) && item.text_sanitized;
    },

    icon() {
      switch (this.item.type) {
        case 'webmention':
          case 'mention':
          case 'trackback':
          case 'pingback':
              return 'commentions-webmention';
          case 'like':
              return 'heart';
          case 'bookmark':
              return 'bookmark';
          case 'reply':
              return 'parent';
          case 'comment':
              return 'chat';
          default:
              return 'circle-outline';
      }
    },

    dateFormatted() {
      return this.$library.dayjs(this.item.timestamp).format("DD.MM.YYYY HH:mm");
    },

    options() {
      return [
        {
          icon: 'check',
          text: this.$t('commentions.section.option.approve'),
          disabled: this.item.status === 'approved',
          click: {
            status: 'approved',
          }
        },
        {
          icon: 'cancel',
          text: this.$t('commentions.section.option.unapprove'),
          disabled: this.item.status === 'unapproved',
          click: {
            status: 'unapproved',
          },
        },
        '-',
        {
          icon: 'url',
          text: this.$t('commentions.section.option.viewsource'),
          link: this.item.website,
          target: '_blank',
          disabled: !this.item.website,
        },
        {
          icon: 'url',
          text: this.$t('commentions.section.option.sendemail'),
          link: `mailto:${this.item.email}`,
          target: '_blank',
          disabled: !this.item.email,
        },
        '-',
        {
          icon: 'trash',
          text: this.$t('commentions.section.option.delete'),
          click: 'delete',
        },
      ];
    }
  },
};
</script>

<style lang="scss">
$list-item-height: 38px;
$breakpoint-small: 30em;
$breakpoint-menu: 45em;
$breakpoint-medium: 65em;
$breakpoint-large: 90em;
$breakpoint-huge: 120em;

.k-commentions-item {
  background: #fff;
  border-radius: 1px;
  box-shadow: var(--box-shadow-item);
  display: flex;
  position: relative;
}

.k-commentions-item-text a {
  text-decoration: underline solid #999;
}

.k-commentions-item-icon {
  flex-shrink: 0;
  height: $list-item-height;
  line-height: 0;
  overflow: hidden;
  width: $list-item-height;
}

.k-commentions-item-icon .k-icon {
  height: $list-item-height;
  left: .2em;
  position: relative;
  width: $list-item-height;
}

.k-commentions-item-icon .k-icon svg {
  opacity: .5;
}

.k-commentions-item-content {
  display: flex;
  flex-grow: 1;
  flex-shrink: 1;
  overflow: hidden;
}

.k-commentions-item-text {
  font-size: var(--font-size-small);
  line-height: 1.25rem;
  padding: .5rem .75rem;
  width: 100%;
}

.k-commentions-item .k-commentions-item-header + .k-commentions-text {
  margin-top: 1.5rem;
}

.k-commentions-item .k-commentions-text {
  padding-bottom: .75rem;
}

.k-commentions-item-header {
  align-items: baseline;
  display: flex;
  flex-wrap: wrap;
  width: 100%;
}

.k-commentions-item-header strong,
.k-commentions-item-header a {
  font-weight: 600;
}

.k-commentions-item-source {
  color: var(--color-text);
  flex-grow: 1;
  font-style: normal;
  margin-right: 1rem;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
}

.k-commentions-item-date {
  color: var(--color-text-light);
  display: block;
  font-size: var(--font-size-tiny);
}

.k-commentions-item-email {
  color: var(--color-text-light);
  font-size: var(--font-size-tiny);
  width: 100%;
}

.k-commentions-item-email a {
  font-weight: 400;
  text-decoration: none;
}

.k-commentions-item-status {
  height: auto !important;
}

.k-commentions-item-options {
  flex-shrink: 0;
  position: relative;
}

.k-commentions-item-options .k-dropdown-content {
  top: $list-item-height;
}

.k-commentions-item-options > .k-button {
  height: $list-item-height;
  padding: 0 12px;
}

.k-commentions-item-options > .k-button > .k-button-icon {
  height: $list-item-height;
}
</style>
