<template>
	<div>
		<h2>欢迎使用中交出行运营平台，请展开左侧功能菜单进行操作</h2>
	</div>
</template>
<script type="text/javascript">

  export default {
		data : function(){
			return {}
		},
		created: function(){
          bus.$emit('globalLoading', false)
		}
	}
</script>