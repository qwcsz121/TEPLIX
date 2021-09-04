<?php

namespace ARPG\entity\monster\walking\EMods;

use ARPG\entity\monster\WalkingMonster;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\nbt\tag\IntTag;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item;
use pocketmine\entity\Creature;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\network\protocol\MobEquipmentPacket;
use pocketmine\network\protocol\MobArmorEquipmentPacket;


class APigZombie extends WalkingMonster{
 
 const NETWORK_ID = 36;

 private $angry = 0;

 public $width = 0.6;
 public $height = 1.8;
 public $eyeHeight = 1.62;

 public function getSpeed() : float{
  
  return isset($this->namedtag["Speed"])? $this->namedtag["Speed"] : 1.2;
 }

 public function initEntity(){
  parent::initEntity();

  if(isset($this->namedtag->Angry)){
   
   $this->angry = (int) $this->namedtag["Angry"];
  }
   
  $this->fireProof = true;
  $this->setDamage([3, 5, 9, 13]);
 }

 public function saveNBT(){
  parent::saveNBT();
   
  $this->namedtag->Angry = new IntTag("Angry", $this->angry);
 }

 public function getName(){
   
  return "PigZombie";
 }

 public function isAngry() : bool{
   
  return $this->angry > 0;
 }

 public function setAngry(int $val){
  
  $this->angry = $val;
 }

 public function targetOption(Creature $creature, float $distance) : bool{

  return $this->isAngry() && parent::targetOption($creature, $distance);
 }

 public function attack($damage, EntityDamageEvent $source){
 
  parent::attack($damage, $source);

  if(!$source->isCancelled()){
   
   $this->setAngry(1000);
  }
 }

 public function spawnTo(Player $player){
  parent::spawnTo($player);

  $pk = new MobEquipmentPacket();
  $pk->eid = $this->getId();
  $pk->item = $this->addEnchant($this->namedtag["Held"]);
  $pk->slot = 10;
  $pk->selectedSlot = 10;
  $player->dataPacket($pk);
      
  $Armor = new MobArmorEquipmentPacket();
  $Armor->eid = $this->getId();
  $Armor->slots = [
    0 => $this->addEnchant($this->namedtag["Cap"]),
    1 => $this->addEnchant($this->namedtag["Tunic"]),
    2 => $this->addEnchant($this->namedtag["Pants"]),
    3 => $this->addEnchant($this->namedtag["Boots"])
  ];
  $player->dataPacket($Armor);
  
 }
    
 public function addEnchant($array){
     
  $item = Item::get($array[0]);
     
  if($array[0] <> 0 and $array[2] > 0){
   
   $enchantment = Enchantment::getEnchantment($array[1])->setLevel($array[2]);
   $item->addEnchantment($enchantment);
  }
  
  return $item;
 }
 
 public function attackEntity(Entity $player){
  if($this->attackDelay > 10 && $this->distanceSquared($player) < 1.44){
   $this->attackDelay = 0;

   $ev = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getDamage());
   $player->attack($ev->getFinalDamage(), $ev);
  }
 }

 public function getDrops(){

  return [];
 }

}