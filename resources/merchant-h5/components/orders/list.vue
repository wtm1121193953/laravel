<template>
    <div class="order-list">
        <el-form :model="query" class="search" size="small" @submit.native.prevent>
            <el-form-item class="form-input">
                <el-input 
                    v-model="query.keyword" 
                    placeholder="请输入内容"
                    prefix-icon="el-icon-search"
                    clearable
                    @focus="showSearch"
                    @blur="hideSearch"
                />
            </el-form-item>
            <el-form-item :class="!isSearch && 'hide'">
                <el-button @click="search" type="primary" size="mini">搜索</el-button>
            </el-form-item>
        </el-form>

        
            <el-col :span="24" class="list-wrapper">
                <scroller
                    :on-infinite="infinite">
                    <div style="height: 1rem;"></div>
                    <div class="item" v-for="(item, index) in list" :key="index">
                        <el-row class="header">
                            <span>{{ item.created_at }}</span>
                            <span v-if="item.status === 1" style="color: #E6A23C;">未支付</span>
                            <span v-else-if="item.status === 2" style="color: #909399;">已取消</span>
                            <span v-else-if="item.status === 3" style="color: #909399;">已关闭[超时自动关闭]</span>
                            <span v-else-if="item.status === 4" style="color: #67C23A;">已支付</span>
                            <span v-else-if="item.status === 5" style="color: #409EFF;">退款中[保留状态]</span>
                            <span v-else-if="item.status === 6" style="color: #909399;">已退款</span>
                            <span v-else-if="item.status === 7" style="color: #67C23A;">已完成</span>
                            <span v-else style="color: #F56C6C;">未知 {{ item.status }}</span>
                        </el-row>
                        <el-row class="content">
                            <div class="avatar" v-if="item.goods_pic" :style="{'background-image': 'url(' + item.goods_pic + ')'}"></div>
                            <div class="info">
                                <div class="left">
                                    <h4>{{ item.goods_name }}</h4>
                                    <p v-if="item.type == 1">类型：团购</p>
                                    <p v-else-if="item.type == 2">类型：买单</p>
                                    <p v-else>类型：未知</p>
                                </div>
                                <div class="right">
                                    <div class="price">￥{{ item.pay_price }}</div>
                                </div>
                            </div>
                        </el-row>
                        <el-row class="other">
                            <p>手机号 : {{ item.notify_mobile }}</p>
                            <p>订单号 : {{ item.order_no }}</p>
                        </el-row>
                        <el-row class="handler" v-if="!!item.remark">
                            <el-button size="mini" round @click="showRemark(item.remark)">备注</el-button>
                        </el-row>
                    </div>
                </scroller>
            </el-col>

        <div class="verification">
            <el-button type="primary" circle @click="showItems">核销</el-button>
        </div>

        <el-dialog title="核销" :visible.sync="isShowItems" width="70%" class="verification-dialog">
            <div>(仅支持一次核销订单全部消费码)</div>
            <el-row>
                <el-col :span="16">
                    <el-input placeholder="请输入消费码" @keyup.native.enter="verification" v-model="verify_code"/>
                </el-col>
                <el-col :span="7" :offset="1">
                    <el-button type="primary" ref="verifyInput" @click="verification">核销</el-button>
                </el-col>
                <div v-if="verify_success">核销成功！<el-button type="text" @click="showDetail(order)">查看订单</el-button></div>
                <div v-if="verify_fail" style="padding-top: 0.2rem; clear: both;">核销失败！请检查消费码</div>
            </el-row>
        </el-dialog>
        
        <div class="convenient">
            <div class="mask" v-if="isConvenient" @click="isConvenient = false"></div>
            <div :class="!isConvenient ? 'container' : 'container action'">
                <div 
                    class="btn-fast" 
                    v-if="!isConvenient"
                    @click="isConvenient = true"
                ><i class="el-icon-d-arrow-left"></i><p>快速<br />导航</p></div>
                <div 
                    class="btn-fast" 
                    v-else
                    @click="isConvenient = false"
                ><i class="el-icon-d-arrow-right"></i>收起</div>

                <ul class="handler-list">
                    <li @click="logout">
                        <i class="el-icon-refresh"></i>
                        <p>退出登录</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    import api from '../../../assets/js/api'
    import OrderForm from './form'

    export default {
        data() {
            return {
                isLoading: false,
                isShow: false,
                isShowItems: false,
                isSearch: false,
                searching: false,
                list: [],
                query: {
                    page: 1,
                    keyword: ''
                },
                total: 0,
                order: {},
                verify_code: '',
                verify_success: false,
                verify_fail: false,
                isGetListEnd: false,
                isConvenient: false
            }
        },
        methods: {
            search(){
                this.query.page = 1;
                this.searching = true;
                this.getList();
            },
            showSearch() {
                //显示搜索按钮
                this.isSearch = true
            },
            hideSearch() {
                //隐藏搜索按钮
                this.isSearch = false
            },
            getList() {
                this.isLoading = true;
                api.get('/orders', this.query).then(data => {
                    console.log(data)
                    if(data.list.length == 0) {
                        //没有数据返回
                        this.isGetListEnd = true;
                        return;
                    }

                    if(this.searching) {
                        this.list = data.list;
                        this.searching = false;
                    } else {
                        this.list = this.list.concat(data.list);
                    }
                    
                    this.total = data.total;
                    this.isLoading = false;
                })
            },
            showDetail(scope) {
                //替换列表-显示核销成功的列表数据
                this.list = [scope]
                this.isShowItems = false;
            },
            showItems() {
                this.isShowItems = true;
                this.verify_success = false;
                this.verify_fail = false;
                this.verify_code = '';
                /*setTimeout(() => {
                    this.$refs.verifyInput.focus();
                }, 1000)*/
            },
            verification(){
                this.verify_success = false;
                this.verify_fail = false;
                if (!this.verify_code){
                    this.$message.error('请填写核销码');
                    return false;
                }
                api.post('/verification', {verify_code: this.verify_code}, false).then(result => {
                    console.log(result);
                    if(result && parseInt(result.code) === 0){
                        this.order = result.data;
                        console.log('order',this.order);
                        this.verify_success = true;
                    }else{
                        this.verify_code = '';
                        this.verify_fail = true;
                        this.$message.error(result.message);
                    }
                })
            },
            infinite(done) {
                //上滑数据拉新
                if(this.isGetListEnd) {
                    setTimeout(() => {
                        done(true)
                    }, 1500)
                    return;
                }

                setTimeout(() => {
                    this.query.page = this.query.page + 1;
                    this.getList();
                    setTimeout(() => {
                        done()
                    })
                }, 1500)
            },
            showRemark(msg) {
                //展示备注
                if(!msg || msg == 'null') {
                    msg = '这家伙好懒，什么都没有留下~'
                }

                this.$message({
                    message: msg,
                    duration: 0,
                    showClose: true
                })
            },
            logout() {
                //退出登录
                this.$confirm('确认退出吗?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消'
                }).then(() => {
                    api.post('/logout').then(() => {
                        this.$message.success('退出成功')
                    })
                    store.dispatch('clearUserInfo');
                    router.replace('/login')
                }).catch(() => {

                })
            }
        },
        created() {
            this.getList();
        },
        components: {
            OrderForm,
        }
    }
</script>

<style lang="less">
    //弹窗
    .el-message-box {
        width: 86%;
    }

    //内容提示信息
    .el-message {
        min-width: 90%;
        max-width: 90%;
        padding: 0.3rem;

        .el-message__content {
            font-size: 0.28rem;
            color: #333;
            line-height: 1.4;
            overflow-wrap: break-word;
            flex: 1;
            width: 88%;
        }
    }

    .order-list {
        width: 100%;

        .search {
            position: relative;
            z-index: 99;
            background-color: #eee;
            padding: 0.15rem;
            display: flex;
            align-items: center;
            justify-content: space-between;

            & > * {
                margin-right: 0 !important;
                margin-bottom: 0 !important;
                margin-left: 0.2rem;

                &:first-child {
                    margin-left: 0;
                }
            }

            .form-input {
                flex: 1;

                .el-form-item__content {
                    display: block!important;
                }

                .el-input__inner {
                    border-radius: 0.5rem;
                    line-height: 1;
                }
            }

            //搜索框获取焦点前-按钮隐藏
            .hide {
                width: 0;
                overflow: hidden;
                margin-left: 0;
            }
        }

        .list-wrapper {
            .item {
                margin-top: 0.2rem;
                background-color: #fff;

                &:nth-child(2) {
                    margin-top: 0;
                }

                .header {
                    display: flex;
                    text-align: center;
                    align-items: center;

                    span {
                        flex: 1;
                        padding: 0.2rem;
                        text-align: center;
                        line-height: 1;
                        font-size: 0.28rem;
                        color: #666;

                        &:first-child {
                            text-align: left;
                        }

                        &:last-child {
                            text-align: right;
                        }
                    }
                }

                .content {
                    padding: 0.16rem 0.2rem;
                    //background-color: #f8f8f8;
                    display: flex;
                    align-items: top;
                    justify-content: space-between;
                    border-top: 0.01rem solid #eee;
                    border-bottom: 0.01rem solid #eee;

                    .avatar {
                        width: 1.2rem;
                        height: 1.2rem;
                        margin-right: 0.2rem;
                    }

                    .info {
                        flex: 1;

                        display: flex;
                        align-items: top;
                        justify-content: space-between;

                        .left {
                            width: 70%;

                            h4 {
                                font-weight: normal;
                                font-size: 0.36rem;
                                line-height: 1.2;

                                //超过两行省略
                                display: -webkit-box;
                                overflow : hidden;
                                text-overflow: ellipsis;
                                -webkit-line-clamp: 2;
                                -webkit-box-orient: vertical;
                            }

                            p {
                                margin-top: 0.08rem;
                                font-size: 0.28rem;
                                color: #999;
                            }
                        }

                        .right {
                            width: 30%;
                            text-align: right;
                            font-size: 0.4rem;
                        }
                    }
                }

                .other {
                    padding: 0.16rem 0.2rem;
                    border-bottom: 0.01rem solid #eee;

                    &:last-child {
                        border-bottom: 0 none;
                    }

                    p {
                        font-size: 0.28rem;
                        text-align: left;
                        color: #999;
                        //让文字可复制
                        //-webkit-user-select: auto;
                    }
                }

                .handler {
                    padding: 0.16rem 0.2rem;
                    text-align: right;
                }
            }
        }

        .verification {
            position: fixed;
            bottom: 0.3rem;
            right: 0.3rem;
            z-index: 99;

            .el-button.is-circle {
                font-size: 0.28rem;
                padding: 0;
                width: 1.12rem;
                height: 1.12rem;
                box-shadow: 0px 3px 5px -1px rgba(0, 0, 0, 0.2),0px 6px 10px 0px rgba(0, 0, 0, 0.14),0px 1px 18px 0px rgba(0, 0, 0, 0.12);
                border-radius: 50%;
            }
        }
        
        .verification-dialog {
            .el-dialog__header {
                padding: 0.4rem 0.4rem 0.2rem;
            }

            .el-dialog__body {
                padding: 0.3rem;

                .el-row {
                    margin-top: 0.2rem;

                    .el-input__inner {
                        line-height: 1;
                    }
                }
            }
        }

        //快速导航
        .convenient {
            position: fixed;
            z-index: 100;
            bottom: 3rem;
            right: 0;

            .mask {
                position: fixed;
                z-index: 98;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.3);
            }

            .container {
                position: relative;
                z-index: 99;
                max-width: 6rem;
                display: flex;
                align-items: center;
                transition: all 0.3s;
                transform: translateX(1.56rem);

                &.action {
                    transform: translateX(0);
                }

                .btn-fast {
                    background-color: rgba(0, 0, 0, 0.6);
                    color: #fff;
                    font-size: 0.2rem;
                    padding: 0 0.08rem;
                    border-top-left-radius: 0.05rem;
                    border-bottom-left-radius: 0.05rem;
                    height: 0.85rem;
                    line-height: 1.2;
                    display: flex;
                    align-items: center;

                    [class^=el-icon-] {
                        margin-right: 0.08rem;
                    }
                }

                .handler-list {
                    padding: 0.2rem 0.3rem;
                    background-color: #fefefe;
                    border-top-left-radius: 0.05rem;
                    border-bottom-left-radius: 0.05rem;
                    flex: 1;
                    display: flex;

                    li {
                        flex: 1;
                        text-align: center;
                        font-size: 0.24rem;
                        color: #999;
                    }
                }
            }
        }
    }
</style>