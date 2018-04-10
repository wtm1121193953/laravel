<template>
    <p class="page-container">404 <br> page not found</p>
</template>

<style>
    .page-container {
        font-size: 20px;
        text-align: center;
        color: rgb(192, 204, 218);
    }
</style>

<script type="text/javascript">
	export default {
		data : function(){
			return {
				id : 1
			}
		},
		created: function(){
            store.commit('setGlobalLoading', false);
		}
	}
</script>