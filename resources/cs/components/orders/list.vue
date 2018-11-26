<template>
    <el-col v-loading="isLoading">
        <el-row align="bottom" type="flex">
            <el-col :span="22">
                <el-form inline :model="query" size="small">
                    <el-form-item label="订单号">
                        <el-input type="text" clearable v-model="query.orderNo" placeholder="输入订单号"/>
                    </el-form-item>
                    <el-form-item label="手机号">
                        <el-input type="text" clearable v-model="query.mobile" placeholder="输入手机号" class="w-150"/>
                    </el-form-item>
                    <el-form-item label="订单状态" v-if="activeTab == 'all'">
                        <el-select v-model="query.status" class="w-100" clearable>
                            <el-option label="全部" value=""/>
                            <el-option label="待发货" value="8"/>
                            <el-option label="待自提" value="9"/>
                            <el-option label="已发货" value="10"/>
                            <el-option label="已完成" value="7"/>
                            <el-option label="已退款" value="6"/>
                        </el-select>
                    </el-form-item>

                    <el-form-item label="支付时间">
                        <el-date-picker
                                class="w-150"
                                v-model="query.startTime"
                                type="date"
                                placeholder="开始日期"
                                value-format="yyyy-MM-dd 00:00:00"
                        />
                        -
                        <el-date-picker
                                class="w-150"
                                v-model="query.endTime"
                                type="date"
                                placeholder="结束日期"
                                value-format="yyyy-MM-dd 23:59:59"
                                :picker-options="{disabledDate: (time) => {return time.getTime() < new Date(query.startTime)}}"
                        />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="search">
                            <i class="el-icon-search"></i> 搜索
                        </el-button>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="success" class="m-l-30" @click="exportExcel">导出Excel</el-button>
                    </el-form-item>
                    <div>
                        <el-form-item>
                            <el-upload
                                :action="actionUrl"
                                :show-file-list="false"
                                :before-upload="beforeUploadExcel"
                                :on-progress="uploadingExcel"
                                :on-success="uploadSuccessExcel"
                            >
                                <el-button type="primary" class="m-l-30">批量发货(200笔以内)</el-button>
                            </el-upload>
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" class="m-l-30" @click="downloadTemplate">批量发货.xls文件模板下载</el-button>
                        </el-form-item>
                    </div>
                </el-form>
            </el-col>
        </el-row>
        <el-table :data="list" stripe>
            <el-table-column prop="pay_time" width="150" label="支付时间"/>
            <el-table-column prop="order_no" width="200" label="订单号"/>
            <el-table-column prop="take_time" label="历时"/>
            <el-table-column prop="type" width="80" label="订单类型">
                <template slot-scope="scope">
                    <span v-if="scope.row.type == 1">团购订单</span>
                    <span v-else-if="scope.row.type == 2">扫码订单</span>
                    <span v-else-if="scope.row.type == 3">点菜订单</span>
                    <span v-else-if="scope.row.type == 4">超市订单</span>
                    <span v-else>其他({{scope.row.type}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="goods_name" width="250" label="商品名称">
                <template slot-scope="scope">
                    <span v-if="scope.row.cs_order_goods.length == 1">
                        {{scope.row.cs_order_goods[0].goods_name}}
                    </span>
                    <span v-else-if="scope.row.cs_order_goods.length > 1">
                        {{scope.row.cs_order_goods[0].goods_name}}等{{getNumber(scope.row.cs_order_goods)}}件商品
                    </span>
                    <span v-else>
                        无
                    </span>
                </template>
            </el-table-column>
            <el-table-column prop="pay_price" label="总价 ¥"/>
            <el-table-column prop="notify_mobile" label="手机号"/>
            <el-table-column prop="status" label="订单状态">
                <template slot-scope="scope">
                    <span v-if="parseInt(scope.row.status) === 1" class="c-danger">未支付</span>
                    <span v-else-if="parseInt(scope.row.status) === 2">已取消</span>
                    <span v-else-if="parseInt(scope.row.status) === 3">已关闭[超时自动关闭]</span>
                    <span v-else-if="parseInt(scope.row.status) === 4" class="c-green" >已支付</span>
                    <span v-else-if="parseInt(scope.row.status) === 5">退款中[保留状态]</span>
                    <span v-else-if="parseInt(scope.row.status) === 6">已退款</span>
                    <span v-else-if="parseInt(scope.row.status) === 7">已完成</span>
                    <span v-else-if="parseInt(scope.row.status) === 8" class="c-danger">待发货</span>
                    <span v-else-if="parseInt(scope.row.status) === 9" class="c-danger">待自提</span>
                    <span v-else-if="parseInt(scope.row.status) === 10" class="c-green">已发货</span>
                    <span v-else>未知 ({{scope.row.status}})</span>
                </template>
            </el-table-column>
            <el-table-column prop="deliver_type" label="发货方式">
                <template slot-scope="scope">
                    <el-popover
                            v-if="scope.row.deliver_type == 1 && scope.row.status != 8"
                            placement="bottom"
                            trigger="hover"
                            width="250"
                    >
                        <div>
                            <div>快递公司：{{scope.row.express_company}}</div>
                            <div>快递单号：{{scope.row.express_no}}</div>
                        </div>
                        <span slot="reference">配送</span>
                    </el-popover>
                    <span v-else-if="scope.row.deliver_type == 1">配送</span>
                    <span v-else-if="scope.row.deliver_type == 2">自提</span>
                    <span v-else>无</span>
                </template>
            </el-table-column>
            <el-table-column prop="merchant_name" width="250" label="商户名称"/>
            <el-table-column label="操作" width="130">
                <template slot-scope="scope">
                    <el-button type="text" @click="showDeliver(scope.row)" v-if="scope.row.status == 8">发货</el-button>
                    <el-button type="text" @click="verification(scope.row)" v-if="scope.row.status == 9">核销</el-button>
                    <el-button type="text" @click="showDetail(scope.row)">订单详情</el-button>
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

        <el-dialog title="订单详情" :visible.sync="isShow">
            <order-form :scope="order"/>
        </el-dialog>

        <el-dialog title="准备发货" :visible.sync="isShowDeliver" center width="25%" :close-on-click-modal="false" :close-on-press-escape="false" @close="closeDeliver">
            <div style="text-align: center; margin-bottom: 15px;">仅限同城配送</div>
            <el-form :model="form" ref="form" :rules="formRules" label-width="100px">
                <el-form-item prop="expressCompany" label="快递公司">
                    <el-input v-model="form.expressCompany" placeholder="50字以内"/>
                </el-form-item>
                <el-form-item prop="expressNo" label="快递编号">
                    <el-input v-model="form.expressNo" placeholder="50字以内"/>
                </el-form-item>
                <div>
                    <p>发货须知：</p>
                    <p>1.为了保证良好的用户体验，商户需要在24H内进行发货，超出24H还未发货，平台有权对商户进行罚款，金额视情况而定。</p>
                    <p>2.商户虚假发货，平台一经查实，订单内商品将下架处理，并承担原订单金额2倍的罚款。</p>
                </div>
            </el-form>
            <span slot="footer" class="dialog-footer">
                <el-button @click="closeDeliver">取 消</el-button>
                <el-button type="primary" @click="deliver">确定发货</el-button>
            </span>
        </el-dialog>
    </el-col>
</template>

<script>
    import api from '../../../assets/js/api'
    import OrderForm from './form'

    export default {
        props: {
            activeTab: {
                required: true,
                type: String,
            },
            status: {
                default: '',
            }
        },
        data() {
            return {
                isLoading: false,
                isShow: false,
                list: [],
                query: {
                    page: 1,
                    orderNo: '',
                    mobile: '',
                    timeType: 'payTime',
                    startTime: '',
                    endTime: '',
                    status: '',
                    merchantType: 2,
                    type: 4,
                },
                total: 0,
                order: {},

                deliverOrder: {},
                isShowDeliver: false,
                form: {
                    expressCompany: '',
                    expressNo: '',
                },
                formRules: {
                    expressCompany: [
                        {required: true, message: '快递公司不能为空'},
                        {max: 50, message: '快递公司不能超过50个字'},
                    ],
                    expressNo: [
                        {required: true, message: '快递编号不能为空'},
                        {max: 50, message: '快递编号不能超过50个字'},
                    ]
                },

                actionUrl: '/api/cs/order/batch/delivery',

                fullscreenLoadingObj: null,
            }
        },
        methods: {
            exportExcel(){
                // 导出操作
                let array = [];
                for (let key in this.query){
                    array.push(key + '=' + this.query[key]);
                }
                location.href = '/api/cs/orders/export?' + array.join('&');
            },
            search(){
                this.query.page = 1;
                this.getList();
            },
            getList() {
                this.isLoading = true;
                api.get('/orders', this.query).then(data => {
                    this.list = data.list;
                    this.total = data.total;

                    this.isLoading = false;
                })
            },
            showDetail(scope) {
                this.order = scope;
                this.isShow = true;
                this.isShowDeliver = false;
            },
            showDeliver(row) {
                this.deliverOrder = row;
                this.isShowDeliver = true;
            },
            closeDeliver() {
                this.isShowDeliver = false;
                this.$refs.form.resetFields();
            },
            deliver() {
                this.$refs.form.validate(valid => {
                    if (valid) {
                        let param = {
                            id: this.deliverOrder.id,
                            expressCompany: this.form.expressCompany,
                            expressNo: this.form.expressNo,
                        };
                        api.post('/order/deliver', param).then(data => {
                            this.$message.success('发货成功');
                            this.closeDeliver();
                            this.getList();
                            this.$emit('refresh');
                        })
                    }
                })
            },
            verification(row){
                this.$prompt('', '自提订单确认', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    center: true,
                    dangerouslyUseHTMLString: true,
                    inputType: 'text',
                    inputPlaceholder: '请输入核销码',
                    inputValidator: (val) => {if(!val || val.length > 6) return '输入核销码，不能超过6位'}
                }).then(({value}) => {
                    let param = {
                        id: row.id,
                        deliver_code: value,
                    };
                    api.post('/order/check/deliver_code', param).then(data => {
                        this.$confirm(`<div><p>订单号：${data.order_no}</p><p>手机号：${data.notify_mobile}</p></div>`, '自提订单确认', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                            center: true,
                            dangerouslyUseHTMLString: true,
                        }).then(() => {
                            api.post('/verification', param).then(data => {
                                this.$message.success('核销成功');
                                this.getList();
                            });
                        }).catch(() => {});
                    })
                }).catch(() => {})
            },
            getNumber(row) {
                let num = 0;
                row.forEach(function (item) {
                    num = num + item.number;
                })
                return num;
            },
            downloadTemplate() {
                api.get('/order/batch_delivery/template').then(data => {
                    location.href = `/api/download?path=${data.url}&as=批量发货.xlsx&code=doc`;
                })
            },
            beforeUploadExcel(file) {
                let type = file.name.split(".").pop();
                if (type != 'xls' && type != 'xlsx') {
                    this.$message.error('只能上传xls或xlsx类型的文件');
                    return false;
                }
                if (file.size > 2 * 1024 * 1024) {
                    this.$message.error('上传文件不能大于2M');
                    return false;
                }
            },
            uploadingExcel() {
                this.fullscreenLoadingObj = this.$loading({
                    text: '系统批量发货处理中，请不要关闭此页面',
                    spinner: 'el-icon-loading',
                    background: 'rgba(0, 0, 0, 0.7)'
                });
            },
            uploadSuccessExcel(res) {
                if (res && res.code === 0) {
                    this.fullscreenLoadingObj.close();
                    this.$message.success('批量发货成功');
                    this.getList();
                    this.$emit('refresh');
                } else {
                    this.fullscreenLoadingObj.close();
                    this.$message.error(res.message || '批量发货失败');
                }
            }
        },
        created() {
            this.query.status = this.status;

            this.getList();
        },
        watch: {
            activeTab() {
                this.getList();
            }
        },
        components: {
            OrderForm,
        }
    }
</script>

<style scoped>

</style>