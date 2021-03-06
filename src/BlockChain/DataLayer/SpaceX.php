<?php


/*
 * This file is part of the blockchain_php.
 * (c) ghost <lvxiang119@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bc\BlockChain\DataLayer;


use Bc\BlockChain\Block;
use Bc\Tools\Log;
use Bc\Tools\Serialize;
use Predis\Client;

class SpaceX
{

    public $spaceHandle;

    public function __construct ()
    {

        $sentinels = [];
        $options = [
            'parameters' => [
                'password' => 'helloworld',
                'database' => 10,
            ],
        ];

        $client = new Client($sentinels, $options);
        $this->spaceHandle = $client;

    }


    public function getBlock ($index)
    {
        return Serialize::unSerialize($this->spaceHandle->get($index));
    }

    public function getCurrentBlockChainHeight ()
    {
        return intval($this->spaceHandle->get('BLOCKCHAIN_CURRENT_H'));
    }

    public function getCurrentBlock ()
    {
        $index = $this->spaceHandle->get('BLOCKCHAIN_CURRENT_H');

        $block = $this->spaceHandle->get($index);

        return Serialize::unSerialize($block);
    }

    public function getBlockByIndex ($index)
    {
        $block = $this->spaceHandle->get($index);

        return Serialize::unSerialize($block);
    }

    public function getCurrentTransactions ()
    {
        $id = 'BLOCKCHAIN_MEMORY_TRANSACTIONS';
        $currentTransactions = $this->spaceHandle->get($id);
        $currentTransactions = Serialize::unSerialize($currentTransactions);

        return $currentTransactions;

    }

    /**
     * 将交易发送至内存池,供打包进入区块
     *
     * @param $transactions
     *
     * @return mixed
     */
    public function appendTransactionsToMemory ($transactions)
    {
        $id = 'BLOCKCHAIN_MEMORY_TRANSACTIONS';

        $currentTransactions = $this->spaceHandle->get($id);
        $currentTransactions = Serialize::unSerialize($currentTransactions);
        $currentTransactions[] = $transactions;

        return $this->spaceHandle->set($id, Serialize::serialize($currentTransactions));

    }

    public function emptyTransactionsInMemory ()
    {
        $id = 'BLOCKCHAIN_MEMORY_TRANSACTIONS';

        return $this->spaceHandle->set($id, null);
    }

    /**
     * 将区块链至区块链
     *
     * @param Block $block
     */
    public function appendToChain (Block $block)
    {
        $id = 'BLOCKCHAIN_CURRENT_H';

        $index = $block->index;
        $this->spaceHandle->set($index, Serialize::serialize($block));
        $this->spaceHandle->set($id, $index);
    }


    public function empty()
    {
        $this->spaceHandle->flushdb();
        $this->emptyTransactionsInMemory();

    }

}