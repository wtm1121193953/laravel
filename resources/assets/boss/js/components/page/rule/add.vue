<template>
    <div>
        <add
                :breadcrumb="{首页: function(){$menu.change('/')}, 权限列表: '/boss/rule/list' }"
                title="添加权限"
                url="/rule/add"
                :fields="[]"
        >

        </add>
    </div>
</template>
<script>
    import add from './add-page.vue'
    export default {
        data: function () {
            return {}
        },
        methods: {},
        created: function () {

        },
        components: {
            add
        }
    }
</script>