import React, {Component} from 'react';

import {Layout, Menu, Switch} from 'element-react';
import Header from './components/Header';
import Footer from './components/Footer';
import LeftMenu from './components/LeftMenu';
import Content from './components/Content';


class Home extends Component {
    constructor(props){
        super(props);

        this.state = {
            theme: 'light', // 系统主题
            menus: [{id: 0, name: '首页', url: '/'}],
            username: 'test username'
        };

        this.onSelect = this.onSelect.bind(this);
    }
    componentDidMount(){
        // todo 判断是否登录, 未登录跳转到登录页面
    }
    onSelect(index){
        console.log(index);
    }
    render(){
        return (<div>
            <Layout.Row>
                <Header/>
            </Layout.Row>
            <Layout.Row>
                <Layout.Col span="3">
                    <LeftMenu
                        theme={this.state.theme}
                        menus={this.state.menus}
                    />

                </Layout.Col>
                <Layout.Col span="21" style={{margin: '20px'}}>
                    <Content/>
                </Layout.Col>
            </Layout.Row>
            <Layout.Row>
                <Footer/>
            </Layout.Row>
        </div>);
    }
}

export default Home;