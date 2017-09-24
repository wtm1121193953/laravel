import React, {Component} from 'react';

import {Layout, Button, Menu, Dropdown} from 'element-react';


class Header extends Component {

    constructor(props){
        super(props);
        this.styleLight = {
            height: '60px',
            backgroundColor: '#eef1f6'
        };
    }

    onSelect(index){
        console.log(index);
    }
    render(){
        return (
            <Layout.Row>
                <Layout.Col span="24" className="el-menu">
                    <Button type="text" style={{float: 'right', margin: '13px 20px' }}>退出登陆</Button>
                    <Dropdown style={{float: 'right', margin: '13px 20px' }} menu={(
                        <Dropdown.Menu>
                            <Dropdown.Item>修改密码</Dropdown.Item>
                            <Dropdown.Item>退出登录</Dropdown.Item>
                        </Dropdown.Menu>
                    )}
                    >
                        <Button type="text">个人中心 <i className="el-icon-caret-bottom"></i></Button>
                    </Dropdown>
                </Layout.Col>
            </Layout.Row>

        );
    }
}

export default Header;