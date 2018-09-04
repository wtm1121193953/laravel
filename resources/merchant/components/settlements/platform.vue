<template>
    <page title="财务管理" v-loading="isLoading">
        <el-col style="margin-bottom: 10px;">
            <el-alert
                    title="温馨提示：单日订单金额小于100元，不生成结算单，总订单金额累计到100元后再生成结算单"
                    type="success"
                    close-text="知道了">
            </el-alert>
        </el-col>
        <el-table :data="list" stripe>
            <el-table-column prop="created_at" label="结算时间" align="center"/>
            <el-table-column min-width="125%" prop="settlement_cycle" label="结算周期" align="center">
                <template slot-scope="scope">
                    {{scope.row.date}} 00:00:00 至 {{scope.row.date}} 23:59:59
                </template>
            </el-table-column>
            <el-table-column prop="amount" label="订单金额 ¥" align="center"/>
            <!--<el-table-column prop="settlement_rate" label="费率" align="center">
                <template slot-scope="scope">
                    {{scope.row.settlement_rate}} %
                </template>
            </el-table-column>-->
            <el-table-column prop="real_amount" label="结算金额 ¥" align="center"/>
            <el-table-column prop="status" label="结算状态" align="center">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.status) === 1">未打款</span>
                    <span v-else-if="parseInt(scope.row.status) === 2">打款中</span>
                    <span v-else-if="parseInt(scope.row.status) === 3">已打款</span>
                    <span v-else-if="parseInt(scope.row.status) === 4">已到账</span>
                    <span v-else-if="parseInt(scope.row.status) === 5">打款失败 （{{scope.row.reason}}）</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column label="操作" width="300px" align="center">
                <template slot-scope="scope">
                    <el-button type="text" v-if="parseInt(scope.row.status) === 2" @click="showDownload(scope, 'cash')">下载回款单</el-button>
                    <el-button type="text" v-if="parseInt(scope.row.status) === 2 && parseInt(scope.row.invoice_type) === 1" @click="showDownload(scope, 'invoice')">下载电子发票</el-button>
                    <el-button type="text" v-if="parseInt(scope.row.status) === 2 && parseInt(scope.row.invoice_type) === 2" @click="showLogistics(scope)">查看纸质发票物流</el-button>
                    <el-button type="text" @click="showOrders(scope)">查看结算详情</el-button>
                </template>
            </el-table-column>
        </el-table>

        <el-pagination
                class="fr m-t-20"
                layout="total, prev, pager, next"
                :current-page.sync="query.page"
                @current-change="getList"
                :page-size="15"
                :total="total"
        />

        <el-dialog :visible.sync="isShowSettlementDetail"  width="60%">
            <settlement-detail :scope="settlement"/>
        </el-dialog>

        <el-dialog title="发票物流信息" :visible.sync="isShowLogistics" center width="20%">
            <el-form>
                <el-form-item label="物流公司：">
                    {{logistics.logistics_name}}
                </el-form-item>
                <el-form-item label="物流订单：">
                    {{logistics.logistics_no}}
                </el-form-item>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button type="primary" @click="isShowLogistics = false">我知道了</el-button>
            </span>
        </el-dialog>

        <el-dialog :title="downloadTitle" :visible.sync="isShowDownloadDialog" width="20%">
            <div v-viewer v-if="downloadImage" v-for="item in downloadImage" class="download-img">
                <img :src="item" width="200px"/>
                <span><el-button type="text" @click="download(item)">下载</el-button></span>
            </div>
        </el-dialog>

    </page>
</template>

<script>
    import api from '../../../assets/js/api'
    import SettlementDetail from './platform-detail'

    export default {
        data() {
            return {
                isLoading: false,
                isShowSettlementDetail: false,
                isShowLogistics: false,
                list: [],
                query: {
                    page: 1,
                },
                total: 0,
                settlement: {},
                logistics: {
                    logistics_name: '',
                    logistics_no: '',
                },
                downloadTitle: '',
                isShowDownloadDialog: false,
                downloadImage: [],
            }
        },
        methods: {
            getList() {
                this.isLoading = true;
                api.get('/settlement/platform', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;
                    this.isLoading = false;
                })
            },
            showOrders(scope) {
                this.settlement = scope.row;
                this.isShowSettlementDetail = true;
            },
            showLogistics(scope) {
                this.logistics.logistics_name = scope.row.logistics_name;
                this.logistics.logistics_no = scope.row.logistics_no;
                this.isShowLogistics = true;
            },
            showDownload(scope, type) {
                console.log(scope.row);
                this.isShowDownloadDialog = true;
                if(type == 'cash'){
                    this.downloadTitle = '下载回款单';
                    this.downloadImage = scope.row.pay_pic_url_arr;
                }else if (type == 'invoice'){
                    this.downloadTitle = '下载电子发票';
                    this.downloadImage = scope.row.invoice_pic_url_arr;
                }
            },
            download(item) {
                location.href = `/download?url=${item}`;
            }
        },
        created() {
            this.getList();
        },
        components: {
            SettlementDetail,
        }
    }
</script>

<style scoped>
    .download-img {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
</style>