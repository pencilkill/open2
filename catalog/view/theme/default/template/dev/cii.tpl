<form method="post" action="<?php echo $action?>">
<table>
<?php if($error){?>
<tr onclick="this.style.display = 'none';"><?php echo $error?></tr>
<?php }?>
<tr>
<td>Cii</td>
<td><input type="text" name="cii" value="<?php echo $cii?>"/></td>
</tr>
<tr>
<td><label><input type="checkbox" name="model" <?php if($model){?>checked="checked"<?php }?>/>Model</label></td>
<td><textarea name="model_methods" rows="5" cols="50"><?php echo $model_methods?></textarea></td>
</tr>
<tr>
<td><label><input type="checkbox" name="controller" <?php if($controller){?>checked="checked"<?php }?>/>Controller</label></td>
<td><textarea name="controller_methods" rows="5" cols="50"><?php echo $controller_methods?></textarea></td>
</tr>
<tr>
<td><label><input type="checkbox" name="language" <?php if($language){?>checked="checked"<?php }?>/>Lanuage</label></td>
<td><textarea name="language_texts" rows="5" cols="50"><?php echo $language_texts?></textarea></td>
</tr>
<tr>
<td><label><input type="checkbox" name="view" <?php if($view){?>checked="checked"<?php }?>/>View</label></td>
<td><textarea name="view_content" rows="10" cols="50"><?php echo $view_content?></textarea></td>
</tr>
</table>
<table>
<tr>
<td align="center"><input type="submit" value="提交"/></td>
<td align="center"><input type="reset" value="重置"/></td>
<td align="center"><input type="button" onclick="location.assign(location.href); return false;" value="清空"/></td>
</tr>
</table>
</form>