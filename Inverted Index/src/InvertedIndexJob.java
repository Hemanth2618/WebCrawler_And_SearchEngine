import java.io.IOException;
import java.util.StringTokenizer;
import java.util.HashMap;
import java.util.Map;
import java.util.Set;

import org.apache.hadoop.fs.Path;
import org.apache.hadoop.io.Text;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Reducer;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;

public class InvertedIndexJob {

  public static class TokenMapper extends Mapper<Object, Text, Text, Text> 
  {
	  private Text word = new Text();
	  private Text docuId = new Text();
	  public void map(Object key, Text value, Context context) throws IOException, InterruptedException 
	  {
		  String split_arr[] = value.toString().split("\t", 2);
		  docuId.set(split_arr[0]);
		  String lowered = split_arr[1].toLowerCase();
		  String replaced = lowered.replaceAll("[^a-z]+", " ");
		  StringTokenizer itr = new StringTokenizer(replaced);
		  
		  while (itr.hasMoreTokens()) 
		  {
			  word.set(itr.nextToken());
			  context.write(word, docuId);
		  }
	  }
  }

  public static class MapReducer extends Reducer<Text,Text,Text,Text> 
  {
	  private Text result = new Text();
	  public void reduce(Text key, Iterable<Text> values, Context context) throws IOException, InterruptedException 
	  {
		  HashMap<Text, Integer> store = new HashMap<Text, Integer>();
		  
		  for (Text val : values) 
		  {
			  Text docId = val;
			  if (store.containsKey(docId))
			  {
				  store.put(docId, store.get(docId) + 1);
			  }
			  else
			  {
				  store.put(docId, 1);
			  }
		  }
		  
		  String cnt = "";
		  Set<Map.Entry<Text, Integer>> s = store.entrySet();
		  for (Map.Entry<Text, Integer> mapEle : s)
		  {
			  cnt += mapEle.getKey() + ":" + mapEle.getValue() + " ";
		  }
		  
		  result.set(cnt);
		  context.write(key, result);
	  }
  }

  public static void main(String[] args) throws Exception 
  {
	  if (args.length != 2) {
		  System.err.println("Usage: Word PostingsList <Input Path> <OutputPath>");
		  System.exit(-1);
	  }
	  Job job = new Job();
	  job.setJarByClass(InvertedIndexJob.class);
	  job.setJobName("Word PostingsList");
	  
	  job.setMapperClass(TokenMapper.class);
	  job.setReducerClass(MapReducer.class);
	  job.setMapOutputKeyClass(Text.class);
	  job.setMapOutputValueClass(Text.class);
	  job.setOutputKeyClass(Text.class);
	  job.setOutputValueClass(Text.class);
	  FileInputFormat.addInputPath(job, new Path(args[0]));
	  FileOutputFormat.setOutputPath(job, new Path(args[1]));
	  System.exit(job.waitForCompletion(true) ? 0 : 1);
  }
}