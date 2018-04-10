<template>
    <div>
        <h2>欢迎使用 {{projectName}} - {{systemName}}，请展开左侧功能菜单进行操作</h2>
    </div>
</template>
<script type="text/javascript">
	import {mapState} from 'vuex'

    export default {
        data: function () {
            return {}
        },
        computed: {
            ...mapState([
                'projectName',
                'systemName',
            ]),
        },
        created: function () {
            bus.$emit('globalLoading', false)
        }
    }
</script>