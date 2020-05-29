<template>
  <section class="k-commentions-section k-section">

    <header class="k-section-header">
      <k-headline>{{ headline }}</k-headline>
      <k-button-group>
        <k-button icon="code" :theme="viewSource ? 'active' : ''" @click="toggleViewSource()">{{ $t('commentions.section.button.viewsource') }}</k-button>
        <k-button icon="refresh" @click="refresh">{{ $t('commentions.section.button.refresh') }}</k-button>
      </k-button-group>
    </header>

    <k-box
      v-for="error in commentionsSystemErrors"
      :key="error.id"
      :theme="error.theme"
    >
      <k-text
        v-html="error.message"
        size="small"
      />
    </k-box>

    <k-commentions-list
      v-if="commentions.length > 0"
    >
        <k-commentions-item
          v-for="item in commentions"
          :key="item.uid"
          :item="item"
          :show="show"
          :view-source="viewSource"
          @action="action"
        />
    </k-commentions-list>

    <k-empty
      v-else
      layout="list"
      icon="chat"
      @click="refresh"
    >
      {{ empty }}
    </k-empty>

    <k-commentions-pagesettings>
      <k-commentions-pagesettingstoggle
        v-for="setting in settings"
        :key="setting.id"
        :setting="setting"
        @change="changePageSetting"
      />
    </k-commentions-pagesettings>

    <k-commentions-edit-dialog
      ref="edit"
      @submit="updateCommention($event)"
    />

    <k-commentions-remove-dialog
      ref="remove"
      @submit="deleteCommention($event)"
    />

  </section>
</template>

<script>
import Item from "./Item.vue";
import List from "./List.vue";
import PageSettings from "./PageSettings.vue";
import PageSettingsToggle from "./PageSettingsToggle.vue";
import RemoveDialog from './Dialogs/RemoveDialog.vue';
import EditDialog from './Dialogs/EditDialog.vue';

export default {
  extends: "k-info-section",

  components: {
    'k-commentions-list': List,
    'k-commentions-item': Item,
    'k-commentions-pagesettings': PageSettings,
    'k-commentions-pagesettingstoggle': PageSettingsToggle,
    'k-commentions-remove-dialog': RemoveDialog,
    'k-commentions-edit-dialog': EditDialog,
  },

  props: {
    // Re-defined from Kirby’s section mixing, because otherwise
    // the section would throw an error, when hot-reloaded during
    // development.
    name: String,
    parent: String
  },

  data() {
    return {
      headline: null,
      commentions: [],
      empty: null,
      show: null,
      errors: [],
      viewSource: false,
      settings: [],
    }
  },

  created() {
    this.load().then((response) => {
      this.headline                = response.headline;
      this.commentions             = response.commentions;
      this.empty                   = response.empty;
      this.commentionsSystemErrors = response.commentionsSystemErrors;
      this.settings                = response.pageSettings;
      this.pageid                  = response.pageId;
      this.show                    = response.show;
    });
  },

  methods: {
    // re-defining load method from Kirby’s section mixin, because
    // it’s not included otherwhise when hot-reloading the module,
    // which leads to an error during development.
    load() {
      return this.$api.get(this.parent + '/sections/' + this.name);
    },

    toggleViewSource() {
      this.viewSource = !this.viewSource;
    },

    action(data, uid, pageid) {
      if (data === 'delete') {
        this.$refs.remove.open(this.commentions.find(item => item.uid === uid));
        return;
      }
      if (data === 'edit') {
        this.$refs.edit.open(this.commentions.find(item => item.uid === uid), pageid);
        return;
      }

      this.updateCommention([pageid, uid, data]);
    },

    async updateCommention(vars) {
      const pageid = vars[0].replace(/\//g, '+');
      const uid = vars[1];
      const endpoint = `commentions/${pageid}/${uid}`;
      const response = await this.$api.patch(endpoint, vars[2]);
      await this.load().then(response => this.commentions = response.commentions);
      this.$store.dispatch("notification/success", ":)");
    },

    async deleteCommention(item) {
      const pageid = item.pageid.replace(/\//g, '+');
      const endpoint = `commentions/${pageid}/${item.uid}`;
      await this.$api.delete(endpoint)
      await this.load().then(response => this.commentions = response.commentions);
      this.$store.dispatch("notification/success", ":)");
    },

    async changePageSetting(key, value) {
      const endpoint = `commentions/pagesettings/` + this.pageid.replace(/\//s, '+');
      const response = await this.$api.patch(endpoint, {key: key, value: value});
    },

    refresh() {
      this.load().then(response => this.commentions = response.commentions);
    }
  },
};
</script>

<style lang="scss">
.k-commentions-section .k-button[data-theme="active"] {
  position: relative;

  &::before {
    background: rgba(#000, .1);
    border-radius: .25rem;
    bottom: .6rem;
    content: "";
    left: .375rem;
    position: absolute;
    right: .375rem;
    top: .7rem;
  }
}

.k-commentions-section > .k-box {
  margin-bottom: 1.5rem;
}

</style>
