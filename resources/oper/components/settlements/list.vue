<template>
    <page title="财务管理" v-loading="isLoading">
        <el-table :data="list" stripe>
            <el-table-column prop="merchant_name" label="结算商户"></el-table-column>
            <el-table-column prop="created_at" label="结算时间"></el-table-column>
        </el-table>
    </page>
</template>

<script>
    import api from '../../../assets/js/api'

    export default {
        data() {
            return {
                isLoading: false,
                list: [],
            }
        },
        methods: {

        },
        created() {

        },
        components: {

        }
    }
</script>