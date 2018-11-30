<template>
    <page title="提现记录">
        <el-tabs type="card" v-model="activeTab">
            <el-tab-pane label="商户提现记录" name="merchant">
                <merchant-record
                    :type="type"
                    :status="status"
                    :queryStartDate="startDate"
                    :queryEndDate="endDate"
                ></merchant-record>
            </el-tab-pane>
            <el-tab-pane label="用户提现记录" name="user">
                <user-record
                    :type="type"
                    :status="status"
                    :queryStartDate="startDate"
                    :queryEndDate="endDate"
                ></user-record>
            </el-tab-pane>
            <el-tab-pane label="运营中心提现记录" name="oper">
                <oper-record
                    :type="type"
                    :status="status"
                    :queryStartDate="startDate"
                    :queryEndDate="endDate"
                ></oper-record>
            </el-tab-pane>
            <el-tab-pane label="业务员提现记录" name="bizer">
                <bizer-record
                        :type="type"
                        :status="status"
                        :queryStartDate="startDate"
                        :queryEndDate="endDate"
                ></bizer-record>
            </el-tab-pane>
            <el-tab-pane label="超市提现记录" name="cs">
                <cs-merchant-record
                        :type="type"
                        :status="status"
                        :queryStartDate="startDate"
                        :queryEndDate="endDate"
                ></cs-merchant-record>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
    import MerchantRecord from './merchant-record'
    import UserRecord from './user-record'
    import OperRecord from './oper-record'
    import BizerRecord from './bizer-record'
    import CsMerchantRecord from './cs-merchant-record'

    export default {
        name: "withdraw-record-index",
        data() {
            return {
                activeTab: 'merchant',
                status: [],
                startDate: '',
                endDate: '',
                type: '',
            }
        },
        created() {
            this.type =  this.$route.query.type;
            if (this.$route.query.type === 'user' || this.$route.query.type === 'oper' || this.$route.query.type === 'bizer' || this.$route.query.type === 'cs') {
                this.activeTab = this.$route.query.type;
            }
            if (this.$route.query.status === 'success') {
                this.status = [3];
            } else if (this.$route.query.status === 'fail') {
                this.status = [4,5];
            }
            if (this.$route.query.startDate) {
                this.startDate = this.$route.query.startDate;
            }
            if (this.$route.query.endDate) {
                this.endDate = this.$route.query.endDate;
            }
        },
        components: {
            MerchantRecord,
            UserRecord,
            OperRecord,
            BizerRecord,
            CsMerchantRecord,
        }
    }
</script>

<style scoped>

</style>