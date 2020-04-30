<template>
  <k-dialog
    ref="dialog"
    :button="$t('delete')"
    theme="negative"
    icon="trash"
    @submit="submit"
  >
    <k-text v-html="$t('user.delete.confirm', { email: user.email })" />
  </k-dialog>
</template>

<script>

export default {
  data() {
    return {
      user: {
        email: null
      }
    };
  },
  methods: {
    open() {
      this.$refs.dialog.open();
      this.$emit("open");
    },

    close() {
      this.$refs.dialog.close();
      this.$emit("close");
    },

    success(payload) {
      this.$refs.dialog.close();

      if (payload.route) {
        this.$router.push(payload.route);
      }

      if (payload.message) {
        this.$store.dispatch("notification/success", payload.message);
      }

      if (payload.event) {
        this.$events.$emit(payload.event);
      }

      this.$emit("success");
    }

    // open(id) {
    //   this.$api.users.get(id)
    //     .then(user => {
    //       this.user = user;
    //       this.$refs.dialog.open();
    //     })
    //     .catch(error => {
    //       this.$store.dispatch('notification/error', error);
    //     });
    // },
    // submit() {
    //   this.$api.users
    //     .delete(this.user.id)
    //     .then(() => {

    //       // remove data froma cache
    //       this.$store.dispatch("content/remove", "users/" + this.user.id);

    //       this.success({
    //         message: ":)",
    //         event: "user.delete"
    //       });

    //       if (this.$route.name === "User") {
    //         this.$router.push("/users");
    //       }
    //     })
    //     .catch(error => {
    //       this.$refs.dialog.error(error.message);
    //     });
    // }
  }
};
</script>
