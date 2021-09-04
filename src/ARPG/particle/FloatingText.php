<?php

namespace ARPG\particle;

use pocketmine\Server;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\RemoveEntityPacket;

class FloatingText{
 
 protected $pos;
 protected $text;
 protected $entityId;
 protected $invisible = true;

 public function __construct(Position $pos, $text = ""){
  
  $this->pos = $pos;
  $this->text = $text;
  $this->entityId = Entity::$entityCount ++;
 }
 
 public function isInvisible(){
  return $this->invisible;
 }
 
 public function spawn(){
 
  $pk = new \pocketmine\network\protocol\AddEntityPacket();
  $pk->eid = $this->entityId;
  $pk->type = 37;
  $pk->x = $this->pos->x;
  $pk->y = $this->pos->y;
  $pk->z = $this->pos->z;
  $pk->speedX = 0;
  $pk->speedY = 0;
  $pk->speedZ = 0;
  
  $flags = 0;
  $flags |= 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG;
  $flags |= 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG;
  $flags |= 1 << Entity::DATA_FLAG_IMMOBILE;
  
  $pk->metadata = [
   Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
   Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->text],
   38 => [7, -1],
   39 => [3, 0.1]
  ];
  
  Server::getInstance()->broadcastPacket($this->pos->level->getPlayers(), $pk);
  
  $this->invisible = true;
 }
 
 public function setText(String $text){
 
  $this->text = $text;
  $this->spawn();
  
  unset($text);
 }
 
 public function respawn(){
 
  $pk = new RemoveEntityPacket();
  $pk->eid = $this->entityId;
  
  Server::getInstance()->broadcastPacket($this->pos->level->getPlayers(), $pk);
  
  $this->invisible = false;
 }
 
}















