import React, { useState, useEffect } from 'react';
import { Modal, Select } from 'antd';
import apiClient from '../api/client';

interface AuxiliaryItem {
    code: string;
    name: string;
    category_code: string;
}

interface AuxiliarySelectorProps {
    open: boolean;
    onCancel: () => void;
    onSelect: (item: AuxiliaryItem) => void;
    categoryCode: string; // The required auxiliary category (e.g., "CUSTOMER")
    subjectName: string;
}

const AuxiliarySelector: React.FC<AuxiliarySelectorProps> = ({ open, onCancel, onSelect, categoryCode, subjectName }) => {
    const [items, setItems] = useState<AuxiliaryItem[]>([]);
    const [loading, setLoading] = useState(false);
    const [searchText, setSearchText] = useState('');

    useEffect(() => {
        if (open && categoryCode) {
            fetchItems();
        }
    }, [open, categoryCode]);

    const fetchItems = () => {
        setLoading(true);
        // Orion filters: field=value
        apiClient.get('/auxiliary-items', {
            params: {
                category_code: categoryCode,
                limit: 100 // Fetch enough items
            }
        }).then(res => {
            setItems(res.data.data);
            setLoading(false);
        }).catch(err => {
            console.error(err);
            setLoading(false);
        });
    };

    const handleSearch = (value: string) => {
        setSearchText(value);
    };

    const filteredItems = items.filter(item =>
        item.code.includes(searchText) || item.name.includes(searchText)
    );

    return (
        <Modal
            title={`选择辅助核算 - ${subjectName}`}
            open={open}
            onCancel={onCancel}
            footer={null}
            destroyOnClose
        >
            <div className="space-y-4">
                <p className="text-slate-400">请选择 <b>{categoryCode}</b> 类别下的辅助核算项目：</p>
                <Select
                    showSearch
                    placeholder="输入编码或名称搜索"
                    optionFilterProp="children"
                    onSearch={handleSearch}
                    onChange={(value) => {
                        const item = items.find(i => i.code === value);
                        if (item) onSelect(item);
                    }}
                    loading={loading}
                    className="w-full"
                    filterOption={false} // We handle filtering manually or via backend if needed, for now local filter
                >
                    {filteredItems.map(item => (
                        <Select.Option key={item.code} value={item.code}>
                            <span className="font-mono mr-2 text-slate-400">{item.code}</span>
                            {item.name}
                        </Select.Option>
                    ))}
                </Select>
            </div>
        </Modal>
    );
};

export default AuxiliarySelector;
