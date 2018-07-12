
<template>
	<el-row>
		<el-col :span="8"><div class="grid-content "></div></el-col>
		<el-col :span="8"><div class="grid-content ">
			<img src="../../assets/images/welcome.png" class="welcome">
			<h2 >欢迎使用大千生活运营平台</h2>
		</div></el-col>
		<el-col :span="8"><div class="grid-content "></div></el-col>
	</el-row>
</template>

<script type="text/javascript">

  export default {

		created: function(){

		}
	}
</script>

<style scoped>
	.grid-content {
		text-align: center;
		border-radius: 4px;
		min-height: 36px;
	}

</style>