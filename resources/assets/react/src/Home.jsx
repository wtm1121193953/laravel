import React, {Component} from 'react';

import {Layout, Menu} from 'element-react';
import Header from './components/Header';
import Footer from './components/Footer';
import LeftMenu from './components/LeftMenu';
import Content from './components/Content';


class Home extends Component {
    constructor(props){
        super(props);
    }
    componentDidMount(){
        // 判断是否登录, 未登录跳转到登录页面
    }
    onSelect(index){
        console.log(e);
    }
    render(){
        return (<div>
            <Layout.Row>
                <Menu mode="horizontal" onSelect={this.onSelect.bind(this)}>
                    <Menu.Item index="1" style={ {float: 'right'} }>处理中心</Menu.Item>
                </Menu>
            </Layout.Row>
            <Layout.Row>
                <Layout.Col span="3">
                    <LeftMenu/>
                </Layout.Col>
                <Layout.Col span="21">
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