<template>
	<div></div>
</template>
<script>
export default {
  created() {
    if (this.$route.query.name) {
        if (this.$route.query.key){
            console.log(this.$route.query.key);
            store.commit('setCurrentMenu', this.$route.query.key);
        }
        console.log('refresh name: ', this.$route.query.name);
      	router.replace({ name: this.$route.query.name, query: this.$route.query.query})
    } else {
      	console.log('refresh fail')
    }
  }
}
</script>