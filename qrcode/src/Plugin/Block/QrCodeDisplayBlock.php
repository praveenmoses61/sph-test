<?php

namespace Drupal\qrcode\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Com\Tecnick\Barcode\Barcode;
use Drupal\file\FileRepositoryInterface;

/**
 * Provides a current time and date display block.
 *
 *
 * @Block(
 *   id = "qr_code_display",
 *   admin_label = @Translation("QR Code Display Block"),
 *   category = @Translation("QR Code"),
 * )
 */
class QrCodeDisplayBlock extends BlockBase implements ContainerFactoryPluginInterface {
  
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * The current route match.
   *
   * @var Drupal\file\FileRepositoryInterface
   */
  protected $FileRepository;

  /**
   * Constructs a new MyBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   The entity type manager service.
   * @param Drupal\file\FileRepositoryInterface $FileRepository
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, CurrentRouteMatch $currentRouteMatch, FileRepositoryInterface $FileRepository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->currentRouteMatch = $currentRouteMatch;
    $this->FileRepository = $FileRepository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
      $container->get('file.repository'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $nid = $this->currentRouteMatch->getParameter('node')->id();
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    $url = $node->field_app_purchase_link->uri;
    $barcode = new Barcode();
    $file_name = time().".png";
    
    // generate a barcode
    $bobj = $barcode->getBarcodeObj(
      'QRCODE,H',                     
      $url,          
      -8,                             
      -8,                             
      'black',                        
      array(-2, -2, -2, -2)           
      )->setBackgroundColor('white');

    $imageData = $bobj->getPngData();
    
    $file = $this->FileRepository->writeData($imageData, "public://".$file_name);

    $markup = "<img src ='/sites/default/files/$file_name' width='150px' height='150px'>";

    return [
      '#markup' => $markup,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}