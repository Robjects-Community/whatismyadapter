// src/Model/Table/AdaptersTable.php
namespace App\Model\Table;

use App\Model\Table\ProductsTable;

class AdaptersTable extends ProductsTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        
        $this->setTable('adapters');
        $this->setEntityClass('App\Model\Entity\Adapter');
        
        // Add adapter-specific behaviors
        $this->addBehavior('ConnectorValidation');
    }
    
    // Adapter-specific methods
    public function findByConnectorType($connectorA, $connectorB)
    {
        return $this->find()
            ->where(['connector_type_a' => $connectorA])
            ->where(['connector_type_b' => $connectorB]);
    }
    
    public function findByPowerDelivery($minWatts)
    {
        return $this->find()
            ->where(['max_power_delivery >=' => $minWatts . 'W']);
    }
}
