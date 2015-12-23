<?php
  
  include '../header.php';
  include '../includes/prettyprint.php';
  
  $personId = isset($_GET['personId']) ? $_GET['personId'] : null;
  
?>

  <form>
    <div class="row">
      <div class="form-group col-sm-6 col-md-4">
        <label for="personIdInput">Person Id</label>
        <input type="text" class="form-control" id="personIdInput" placeholder="Person Id" name="personId" value="<?= $personId; ?>">
      </div>
    </div>
    <button type="submit" class="btn btn-primary">Read Person</button>
  </form>
  <br>

<?php
  
  if($personId){
  
    // First we make a request to the API for the person and save the response
    $response = $client->familytree()->readPersonById($personId);
    
    // Check for errors
    if($response->hasError()){
      handleErrors($response);
    }
    
    // No errors
    else {
      // Then we get the person from the response
      $person = $response->getPerson();
      
      // Then we trasnition to the memories (artifacts) from the person response
      $memoriesResponse = $response->readArtifacts();
      
      // Then we pull the GEDCOM X document from the response
      $gedx = $memoriesResponse->getEntity();
      
      // Memories are listed as source descriptions in the gedcomx document
      // See https://familysearch.org/developers/docs/api/tree/Read_Person_Memories_usecase
      foreach($gedx->getSourceDescriptions() as $memory){
        ?>
        <div class="panel panel-default">
          <div class="panel-heading"><code><?= $memory->getMediaType(); ?></code></div>
          
          
          <table class="table">
            <?php if ($memory->getMediaType() == "image/jpeg"){?>
            <tr>
              <td><img src="<?= $memory->getLink('image')->getHref() ?>"></td>
            </tr>
            <?php } elseif ($memory->getMediaType() == "text/plain"){ ?>
            <tr>
              <th>Title:</th>
              <td><?= $memory->getTitles()[0]->getValue() ?></td>
            </tr>
            <tr>
              <th>Body:</th>
              <td><?= $memory->getDescriptions()[0]->getValue() ?></td>
            </tr>
            <?php } ?>
          </table>

        </div>
        <?php
        
        rawDump($memory);
      }
    
    }
  
  }
  
  include '../footer.php';