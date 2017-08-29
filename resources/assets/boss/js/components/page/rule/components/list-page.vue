<template>
    <div>
        <list-page v-if="type=='page'"
              :breadcrumb="breadcrumb"
              :title="title"
              :dataUrl="dataUrl"
              :searchForm="searchForm"
              :columns="columns"
              :addBtn="addBtn"
              :exportBtn="exportBtn"
              :edit="edit"
              :del="del"
              :rowOptions="rowOptions"
              :disablePage="disablePage"
              :batchOptions="batchOptions"
              @add="$emit('add')"
              @export="$emit('export')"
              @search="$emit('search')"
              @edit="$emit('edit')"
              @del="$emit('del')"
        >
            <template slot="search"><slot name="search"></slot></template>
        </list-page>
        <list-dialog v-if="type=='dialog'"
                :visible.sync="dialogVisible"
                :title="title"
                :dataUrl="dataUrl"
                :searchForm="searchForm"
                :columns="columns"
                :addBtn="addBtn"
                :exportBtn="exportBtn"
                :edit="edit"
                :del="del"
                :rowOptions="rowOptions"
                :disablePage="disablePage"
                :batchOptions="batchOptions"
                @add="$emit('add')"
                @export="$emit('export')"
                @search="$emit('search')"
                @edit="$emit('edit')"
                @del="$emit('del')"
                @select="$emit('select')"
        >
            <template slot="search"><slot name="search"></slot></template>
            <template slot="footer"><slot name="footer"></slot></template>
        </list-dialog>
    </div>
</template>
<script>
    import listPage from './list-page-style.vue'
    import listDialog from './list-dialog-style.vue'

    /*
     * 事件列表 : add, export, search, edit, del, select
     * 属性列表
     *      type: 列表类型 page|dialog  页面或者弹出框, 默认为页面
     *      visible: 页面是否可见, 需要使用 :visible.sync="isVisible" 的方式绑定
     *      breadcrumb: 页面面包屑, 不需要包含当前页面的标题
     *      title: 页面标题 必须
     *      dataUrl: 数据获取接口地址 必须
     *      searchForm: 搜索表单, 获取数据时会把搜索表单的数据加入到查询中
     *      columns: 要展示的数据列 必须
     *      addBtn: 添加数据按钮的文字, 布尔值或字符串, 值为true时按钮上的文字为默认的'添加数据', false不显示按钮 默认: false
     *      exportBtn: 导出按钮的文字, 布尔值或字符串, 值为true时按钮上的文字为默认的'导出数据', false不显示按钮 默认: false
     *      edit: 列表中是否有编辑选项  默认: 有
     *      del: 列表中是否有删除选项  默认: 有
     *      rowOptions: 列表每一行的其他操作项  可以为空
     *      disablePage: 是否禁用分页  默认不禁用
     *      batchOptions: 批量操作
     */
    export default {
        props: {
            type: {type: String, default: 'page'},
            visible: {type: Boolean, default: false},
            breadcrumb: {type: Object},
            title: { type: String, required: true },
            dataUrl: {type: String, required: true},
            searchForm: {type: Object, default: null},
            columns: {type: Object, required: true},
            exportBtn: {type: [Boolean, String], default: false},
            addBtn: {type: [Boolean, String], default: false},
            edit: {type: [Boolean, Function], default: false},  // 是否有编辑选项
            del: {type: [Boolean, Function], default: false},  // 是否有编辑选项
            selectable: {type: [Boolean, Function], default: true}, // 选择操作
            rowOptions: {type: Object},
            disablePage: {type: Boolean, default: false},
            batchOptions: {type: Object, default: null},
        },
        data: function () {
            return {
                dialogVisible: false,
            }
        },
        methods: {

        },
        watch: {
            dialogVisible(value){
                this.$emit('update:visible', value)
            },
            visible(value){
                this.dialogVisible = value;
            }
        },
        created: function () {
        },
        components:{
            listPage,
            listDialog
        }
    }
</script>