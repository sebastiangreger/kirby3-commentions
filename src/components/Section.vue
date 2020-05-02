<template>
  <section class="k-commentions-section k-section">

    <!-- <k-info-field
      label="⚠️ Missing dependencies"
      text="Some dependencies are missing"
      theme="negative">
    </k-info-field> -->

    <header class="k-section-header">
      <k-headline>{{ headline }}</k-headline>
      <k-button-group>
        <k-button icon="code" :theme="viewSource ? 'active' : ''" @click="toggleViewSource()">View source</k-button>
        <k-button icon="refresh" @click="refresh">Refresh</k-button>
      </k-button-group>
    </header>

    <k-box theme="negative" v-if="error">
      <k-text size="small" v-if="error == 'version'">
        <strong>Action required!</strong> You updated the <em>Kirby3-Commentions</em> plugin to version 1.x, but your setup is still in the (now incompatible) 0.x format! Worry not: no data has been lost, but you will have to use the <a href="/commentions-migrationassistant" target="_blank">Migration assistant</a> to get things running again!
      </k-text>
      <k-text size="small" v-else>
        {{ error }}
      </k-text>
    </k-box>

    <k-commentions-list
      v-if="commentions.length > 0"
    >
        <k-commentions-item
          v-for="item in commentions"
          :key="item.uid"
          :item="item"
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

    <k-commentions-remove-dialog
      ref="remove"
      @submit="deleteCommention($event)"
    />

  </section>
</template>

<script>
import Item from "./Item.vue";
import List from "./List.vue";
import RemoveDialog from './Dialogs/RemoveDialog.vue';

export default {
  extends: "k-info-section",

  components: {
    'k-commentions-list': List,
    'k-commentions-item': Item,
    'k-commentions-remove-dialog': RemoveDialog,
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
      error: null,
      viewSource: false,
    }
  },

  created() {
    this.load().then((response) => {
      this.headline    = response.headline;
      this.commentions = response.commentions;
      this.empty       = response.empty;
      this.error       = response.error;
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

      this.updateCommention(pageid, uid, data);
    },

    async updateCommention(pageid, uid, data) {
      pageid = pageid.replace(/\//s, '+');
      const endpoint = `commentions/${pageid}/${uid}`;
      const response = await this.$api.patch(endpoint, data);

      await this.load().then(response => this.commentions = response.commentions);
      this.$store.dispatch("notification/success", ":)");
    },

    async deleteCommention(item) {
      const pageid = item.pageid.replace(/\//s, '+');
      const endpoint = `commentions/${pageid}/${item.uid}`;
      await this.$api.delete(endpoint)
      await this.load().then(response => this.commentions = response.commentions);
      this.$store.dispatch("notification/success", ":)");
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
</style>
